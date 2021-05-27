<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Model;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Magento\Framework\Model\AbstractModel;
use Bss\BrandRepresentative\Model\ResourceModel\MostViewed as ResourceModel;

/**
 * Class MostViewed
 */
class MostViewed extends AbstractModel implements MostViewedInterface
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
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($val)
    {
        return $this->setData(self::ENTITY_ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return $this->getData(self::ENTITY_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setEntityType($val)
    {
        return $this->setData(self::ENTITY_TYPE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getTraffic()
    {
        return $this->getData(self::TRAFFIC);
    }

    /**
     * @inheritDoc
     */
    public function setTraffic($val)
    {
        return $this->setData(self::TRAFFIC, $val);
    }
}
