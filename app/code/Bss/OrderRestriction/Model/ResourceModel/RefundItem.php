<?php
declare(strict_types=1);
namespace Bss\OrderRestriction\Model\ResourceModel;

use Bss\OrderRestriction\Api\Data\RefundItemInterface;

/**
 * Class RefundItem
 * Resource Model
 */
class RefundItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE = "bss_customer_refund_items";

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, RefundItemInterface::ID);
    }

    /**
     * Get exist refund product data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $orderId
     * @param int $productId
     * @param int $customerId
     * @return $this
     */
    public function getByOrderProductAndCustomer(
        \Magento\Framework\Model\AbstractModel $object,
        $orderId,
        $productId,
        $customerId
    ) {
        $select = $this->getConnection()->select();
        $select->from(
            $this->getTable('bss_customer_refund_items'),
            ['id']
        )->where("order_id=?", $orderId)->where("product_id=?" ,$productId)->where("customer_id=?", $customerId);

        $refundItemId = $this->getConnection()->fetchRow($select);

        if ($refundItemId) {
            $this->load($object, $refundItemId);
        }

        return $this;
    }
}
