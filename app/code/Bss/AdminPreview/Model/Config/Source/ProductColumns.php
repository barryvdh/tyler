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
 * @category   BSS
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class ProductColumns
 * @package Bss\AdminPreview\Model\Config\Source
 */
class ProductColumns implements ArrayInterface
{
    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        return [
            'sku' => __('Sku'),
            'name' => __('Product Name'),
            'image' => __('Image'),
            'original_price' => __('Original Price'),
            'price' => __('Price'),
            'qty_ordered' => __('Order Items Quantity'),
            'subtotal' => __('Subtotal'),
            'tax_amount' => __('Tax Amount'),
            'tax_percent' => __('Tax Percent'),
            'row_total_incl_tax' => __('Row Total'),
        ];
    }
}