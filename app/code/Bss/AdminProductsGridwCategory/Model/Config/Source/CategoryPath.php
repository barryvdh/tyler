<?php
/**
 *
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
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminProductsGridwCategory\Model\Config\Source;

/**
 * Class CategoryPath
 *
 * @package Bss\AdminProductsGridwCategory\Model\Config\Source
 */
class CategoryPath
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'full_path', 'label' => __('Full Category Path')],
            ['value' => 'short_path', 'label' => __('Short Category Path')],
        ];
    }
}
