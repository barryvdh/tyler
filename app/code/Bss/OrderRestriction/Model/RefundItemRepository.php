<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\Data\RefundItemInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Repository class
 */
class RefundItemRepository implements \Bss\OrderRestriction\Api\RefundItemRepositoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ResourceModel\RefundItem
     */
    protected $refundItemResource;
    /**
     * @var RefundItemFactory
     */
    protected $refundItemFactory;

    /**
     * RefundItemRepository constructor.
     *
     * @param LoggerInterface $logger
     * @param ResourceModel\RefundItem $refundItemResource
     * @param RefundItemFactory $refundItemFactory
     */
    public function __construct(
        LoggerInterface $logger,
        \Bss\OrderRestriction\Model\ResourceModel\RefundItem $refundItemResource,
        \Bss\OrderRestriction\Model\RefundItemFactory $refundItemFactory
    ) {
        $this->logger = $logger;
        $this->refundItemResource = $refundItemResource;
        $this->refundItemFactory = $refundItemFactory;
    }

    /**
     * @inheritDoc
     */
    public function get($orderId, $productId, $customerId)
    {
        $refundItem = $this->refundItemFactory->create();
        try {
            $this->refundItemResource->getByOrderProductAndCustomer($refundItem, $orderId, $productId, $customerId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $refundItem;
    }

    /**
     * @inheritDoc
     */
    public function save(RefundItemInterface $refundItem)
    {
        try {
            $this->refundItemResource->save($refundItem);

            return $refundItem;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(__("Something went wrong! Please review the log."));
        }
    }
}
