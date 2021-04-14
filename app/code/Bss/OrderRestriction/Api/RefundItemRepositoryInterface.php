<?php
declare(strict_types=1);
namespace Bss\OrderRestriction\Api;

use Bss\OrderRestriction\Api\Data\RefundItemInterface;

/**
 * Interface RefundItemRepositoryInterface
 */
interface RefundItemRepositoryInterface
{
    /**
     * Get exist refund product by order and customer
     *
     * @param int $orderId
     * @param int $productId
     * @param int $customerId
     * @return RefundItemInterface
     */
    public function get($orderId, $productId, $customerId);

    /**
     * Save object
     *
     * @param RefundItemInterface $refundItem
     * @return RefundItemInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(RefundItemInterface $refundItem);
}
