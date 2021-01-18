<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

use Bss\OrderRestriction\Api\Data\OrderRuleInterface;

/**
 * Class OrderRule Resource
 */
class OrderRule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE = 'bss_order_restriction';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, OrderRuleInterface::CUSTOMER_ID);
    }
}
