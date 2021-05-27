<?php
declare(strict_types=1);
namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\Data\RefundItemInterface;
use Magento\Framework\Model\AbstractModel;
use Bss\OrderRestriction\Model\ResourceModel\RefundItem as ResourceModel;

/**
 * Class RefundItem
 * Class Model
 */
class RefundItem extends AbstractModel implements RefundItemInterface
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
    public function setId($val)
    {
        return $this->setData(self::ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderId($val)
    {
        return $this->setData(self::ORDER_ID, $val);
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
    public function setCustomerId($val)
    {
        return $this->setData(self::CUSTOMER_ID, $val);
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
    public function setProductId($val)
    {
        return $this->setData(self::PRODUCT_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function setQty($val)
    {
        return $this->setData(self::QTY, $val);
    }
}
