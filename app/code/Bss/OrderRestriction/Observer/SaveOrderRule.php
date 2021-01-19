<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Observer;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Magento\Framework\Event\Observer;

/**
 * Save the order rule if the request contains
 */
class SaveOrderRule implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        OrderRuleRepositoryInterface $orderRuleRepository
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->orderRuleRepository = $orderRuleRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            $orderRuleRequest = $this->request->getParam('order_restriction');

            if ($orderRuleRequest) {
                array_walk(
                    $orderRuleRequest,
                    function (&$param) {
                        if ($param == "") {
                            $param = null;
                        }
                    }
                );
                $orderRule = $this->orderRuleRepository->get($orderRuleRequest["entity_id"]);

                if ($orderRule->getOrdersPerMonth() == $orderRuleRequest['orders_per_month'] &&
                    $orderRule->getQtyPerOrder() == $orderRuleRequest['qty_per_order']
                ) {
                    return $this;
                }

                $customerId = $this->request->getParam('customer')['entity_id'];
                $orderRule->setCustomerId($customerId);
                $orderRule->setOrdersPerMonth($orderRuleRequest['orders_per_month']);
                $orderRule->setQtyPerOrder($orderRuleRequest['qty_per_order']);

                $this->orderRuleRepository->save($orderRule);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
