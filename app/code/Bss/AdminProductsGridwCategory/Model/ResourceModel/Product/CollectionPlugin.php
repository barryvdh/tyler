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

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Bss\AdminProductsGridwCategory\Model\ResourceModel\CategoryProduct\CollectionFactory as CategoryProduct;

class CollectionPlugin
{
    const SORT_ORDER_ASC = 'ASC';
    const SORT_ORDER_DESC = 'DESC';

    /**
     * @param Collection $subject
     * @param callable $proceed
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function aroundAddAttributeToSort(
        Collection $subject,
        $proceed,
        $field,
        $direction = self::SORT_ORDER_DESC
    ) {
        if ($field == 'category_id') {
            $subject->getSelect()
                ->joinLeft(
                    ['sec' => $subject->getTable('catalog_category_product')],
                    'e.entity_id = sec.product_id ',
                    ['sec.category_id']
                )
                ->where('sec.category_id = (SELECT MIN(category_id) FROM '
                    . $subject->getTable('catalog_category_product') . ' sec2 WHERE sec2.product_id = e.entity_id)')
                ->order(
                    'sec.category_id ' . $direction
                );
        }
        if ($field == 'category') {
            $subject->getSelect()
                ->joinLeft(
                    ['sec' => $subject->getTable('catalog_category_product')],
                    'e.entity_id = sec.product_id ',
                    ['sec.category_id']
                )
                ->joinLeft(
                    ['thir' => $subject->getTable("catalog_category_entity_varchar")],
                    'thir.entity_id = sec.category_id',
                    ['thir.value']
                )
                ->where(
                    'sec.category_id=(SELECT MIN(category_id) FROM '
                        . $subject->getTable('catalog_category_product') . ' sec2 WHERE sec2.product_id = e.entity_id) '
                )
                ->where('thir.value_id=(SELECT MIN(value_id)  FROM '
                    . $subject->getTable('catalog_category_entity_varchar') . ' WHERE  entity_id = thir.entity_id)')
                ->order(
                    'thir.value ' . $direction
                );
            $subject->getSelect()->__toString();
        }

        return $proceed($field, $direction);
    }
}
