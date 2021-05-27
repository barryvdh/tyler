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
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($value)
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }

    /**
     * @inheritDoc
     */
    public function getSaleQtyPerMonth()
    {
        return $this->getData(self::SALE_QTY_PER_MONTH);
    }

    /**
     * @inheritDoc
     */
    public function setSaleQtyPerMonth($val)
    {
        return $this->setData(self::SALE_QTY_PER_MONTH, $val);
    }

    /**
     * @inheritDoc
     */
    public function getUseConfig()
    {
        return $this->getData(self::USE_CONFIG);
    }

    /**
     * @inheritDoc
     */
    public function setUseConfig($val)
    {
        return $this->setData(self::USE_CONFIG, $val);
    }
}
