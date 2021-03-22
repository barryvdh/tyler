<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Model\ResourceModel\Product;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Bss\BrandRepresentative\Model\ResourceModel\MostViewed;

class Collection extends \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection
{
    /**
     * Add category traffic value
     *
     * @return Collection
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('traffic', 'most_viewed.traffic');
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['most_viewed' => $this->getResource()->getTable(MostViewed::TABLE)],
            'most_viewed.' . MostViewedInterface::ENTITY_ID . ' = e.entity_id AND most_viewed.'
            . MostViewedInterface::ENTITY_TYPE . '=' . MostViewedInterface::TYPE_PRODUCT,
            ['traffic' => new \Zend_Db_Expr('COALESCE(most_viewed.traffic, 0)')]
        );

        return $this;
    }
}
