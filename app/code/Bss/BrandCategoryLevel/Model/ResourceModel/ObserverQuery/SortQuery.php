<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Bss\BrandRepresentative\Model\ResourceModel\MostViewed;

class SortQuery
{
    private $toolbar;

    /**
     * SortQuery constructor.
     *
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
     */
    public function __construct(
        \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbar
    ) {
        $this->toolbar = $toolbar;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function sortByMostViewed($collection)
    {
        $collection->getSelect()->reset('order')->joinLeft(
            ['most_viewed' => $collection->getTable(MostViewed::TABLE)],
            'most_viewed.' . MostViewedInterface::ENTITY_ID . ' = e.entity_id AND most_viewed.'
            . MostViewedInterface::ENTITY_TYPE . '=\'' . MostViewedInterface::TYPE_PRODUCT . '\'',
            ['traffic' => new \Zend_Db_Expr('COALESCE(most_viewed.traffic, 0)')]
        )
            ->order([new \Zend_Db_Expr('traffic IS NULL asc, traffic desc')]);

        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function sortByNewest($collection)
    {
        $collection->getSelect()->reset('order')->order('created_at DESC');

        return $this;
    }
}
