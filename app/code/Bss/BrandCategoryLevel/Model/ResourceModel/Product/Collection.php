<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Model\ResourceModel\Product;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Bss\BrandRepresentative\Model\ResourceModel\MostViewed;

class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection
{
    /**
     * Add category traffic value
     *
     * @return Collection
     */
    protected function _initSelect()
    {
        dd(1);
        $this->addAttributeToSort('created_at', 'desc');
        // $this->addFilterToMap('traffic', 'most_viewed.traffic');
        return parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['most_viewed' => $this->getResource()->getTable(MostViewed::TABLE)],
            'most_viewed.' . MostViewedInterface::ENTITY_ID . ' = e.entity_id AND most_viewed.'
            . MostViewedInterface::ENTITY_TYPE . '=' . MostViewedInterface::TYPE_PRODUCT,
            ['traffic' => new \Zend_Db_Expr('COALESCE(most_viewed.traffic, 0)')]
        );

        return $this;
    }

    protected function _renderFiltersBefore()
    {
        dd(1);
    }

    public function hahaha()
    {
        return "2312312312123123";
    }
}
