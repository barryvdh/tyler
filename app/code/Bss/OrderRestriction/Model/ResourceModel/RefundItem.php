<?php
declare(strict_types=1);
namespace Bss\OrderRestriction\Model\ResourceModel;

use Bss\OrderRestriction\Api\Data\RefundItemInterface;

class RefundItem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE = "customer_refund_items";

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, RefundItemInterface::ID);
    }
}
