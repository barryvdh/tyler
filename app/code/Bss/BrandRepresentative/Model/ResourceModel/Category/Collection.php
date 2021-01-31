<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Model\ResourceModel\Category;

use Bss\BrandRepresentative\Model\ResourceModel\MostViewed;

/**
 * Class Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Category\Collection
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
            'most_viewed.category_id = e.entity_id',
            ['traffic' => new \Zend_Db_Expr('COALESCE(most_viewed.traffic, 0)')]
        );

        return $this;
    }
}
