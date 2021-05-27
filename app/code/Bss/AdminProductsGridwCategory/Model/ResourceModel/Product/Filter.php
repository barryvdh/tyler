<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Model\ResourceModel\Product;

use Magento\Framework\App\ResourceConnection;
use Bss\AdminProductsGridwCategory\Model\ResourceModel\CategoryProduct\CollectionFactory as CategoryProduct;
use Magento\Catalog\Model\CategoryFactory as Category;

class Filter
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var CategoryProduct
     */
    protected $categoryProductCollection;

    /**
     * @var Category
     */
    protected $categoryCollection;
    /**
     * @var string
     */
    protected $data = [];

    /**
     * Filter constructor.
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource,
        CategoryProduct $categoryProductCollection,
        Category $categoryCollection
    ) {
        $this->resource = $resource;
        $this->categoryProductCollection = $categoryProductCollection;
        $this->categoryCollection = $categoryCollection;
    }

    /**
     * @param $field
     * @param $value
     * @return string
     */
    public function catFilter($field, $value)
    {
        $texts = explode(',', $value);
        if ($texts[0] == '&') {
            unset($texts[0]);
            $this->searchAnd($texts, $field);
        } else {
            $this->searchOr($value, $field);
        }
        $listId = '(' . implode(',', $this->data) . ')';
        return $listId;
    }

    /**
     * @param $texts
     * @param $field
     */
    protected function searchAnd($texts, $field)
    {
        $data = [];
        if ($field == 'category') {
            $categoryCollection = $this->categoryCollection->create()->getCollection();
            $cond = [];
            foreach ($texts as $text) {
                $cond[] = ['like' => trim($text)];
            }
            $categoryCollection->addAttributeToFilter('name', $cond);
            $categoryIds = array_keys($categoryCollection->getItems());
            foreach ($categoryIds as $cateId) {
                $collection = $this->categoryProductCollection->create()->addFieldToFilter('category_id', $cateId);
                if (empty($data)) {
                    $data = $this->fetchData($collection);
                } else {
                    $data = array_intersect($data, $this->fetchData($collection));
                }
            }
        }
        if ($field == 'category_id') {
            foreach ($texts as $cateId) {
                $collection = $this->categoryProductCollection->create()->addFieldToFilter('category_id', $cateId);
                if (empty($data)) {
                    $data = $this->fetchData($collection);
                } else {
                    $data = array_intersect($data, $this->fetchData($collection));
                }
            }
        }

        $this->data = $data;
    }

    /**
     * @param $value
     * @param $field
     */
    protected function searchOr($value, $field)
    {
        $collection = $this->categoryProductCollection->create();
        $texts = explode(',', $value);
        if ($field == 'category') {
            $categoryCollection = $this->categoryCollection->create()->getCollection();
            $cond = [];
            foreach ($texts as $text) {
                $cond[] = ['like' => trim($text)];
            }
            $categoryCollection->addAttributeToFilter('name', $cond);
            $categoryIds = array_keys($categoryCollection->getItems());
            $collection->addFieldToFilter('category_id', ['in' => $categoryIds]);
        }

        if ($field == 'category_id') {
            $collection->addFieldToFilter('category_id', ['in' => $texts]);
        }

        $this->data = $this->fetchData($collection);
    }

    /**
     * @param \Bss\AdminProductsGridwCategory\Model\ResourceModel\CategoryProduct\Collection $collection
     * @return array
     */
    protected function fetchData($collection)
    {
        $data = [0];
        foreach ($collection as $record) {
            $data[] = $record->getData('product_id');
        }
        return $data;
    }
}
