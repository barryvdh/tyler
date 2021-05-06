<?php
/**
 * Class for Restrictcustomergroup Toolbar
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin\Catalog\Block\Product\ProductList;

class ToolbarPlugin
{
    public function afterGetTotalNum(\Magento\Catalog\Block\Product\ProductList\Toolbar $subject, $size)
    {
        $size = $subject->getCollection()
                ->getAllIds();

        return count($size);
    }
}
