<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Observer;

use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

/**
 * Class BackToStockQtyObserver
 * Add refunded qty to bss customer refund items if the refunded item is back to stock
 */
class BackToStockQtyObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $returnProcessor;

    /**
     * BackToStockQtyObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Bss\OrderRestriction\Model\Order\RefundItemProcessor $returnProcessor
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Bss\OrderRestriction\Model\Order\RefundItemProcessor $returnProcessor
    ) {
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->returnProcessor = $returnProcessor;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $this->orderRepository->get($creditmemo->getOrderId());
        $returnToStockItems = [];
        foreach ($creditmemo->getItems() as $item) {
            if ($item->getBackToStock()) {
                $returnToStockItems[] = $item->getOrderItemId();
            }
        }
        if (!empty($returnToStockItems)) {
            $this->returnProcessor->execute($creditmemo, $order, $returnToStockItems);
        }
    }
}
