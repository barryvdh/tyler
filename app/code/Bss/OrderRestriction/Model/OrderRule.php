<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\Data\OrderRuleInterface;
use Magento\Framework\Model\AbstractModel;
use Bss\OrderRestriction\Model\ResourceModel\OrderRule as ResourceModel;

/**
 * Class OrderRule model
 */
class OrderRule extends AbstractModel implements OrderRuleInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getQtyPerOrder()
    {
        return $this->getData(self::QTY_PER_ORDER);
    }

    /**
     * @inheritDoc
     */
    public function setQtyPerOrder($val)
    {
        return $this->setData(self::QTY_PER_ORDER, $val);
    }

    /**
     * @inheritDoc
     */
    public function getOrdersPerMonth()
    {
        return $this->getData(self::ORDERS_PER_MONTH);
    }

    /**
     * @inheritDoc
     */
    public function setOrdersPerMonth($val)
    {
        return $this->setData(self::ORDERS_PER_MONTH, $val);
    }
}
