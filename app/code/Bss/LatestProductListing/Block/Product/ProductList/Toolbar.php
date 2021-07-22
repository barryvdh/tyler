<?php
declare(strict_types=1);
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
 * @package    Bss_LatestProductListing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\LatestProductListing\Block\Product\ProductList;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    const DEFAULT_LIMIT_PER_PAGE = 24;

    protected $_template = "Bss_LatestProductListing::product/list/toolbar.phtml";

    /**
     * Default 24 product in page
     *
     * @return string[]
     */
    public function getAvailableLimit()
    {
        return [
            '24' => '24'
        ];
    }

    /**
     * Set default limit perpage before set collection
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return Toolbar
     */
    public function setCollection($collection)
    {
        $this->setData("_current_limit", static::DEFAULT_LIMIT_PER_PAGE);
        return parent::setCollection($collection);
    }
}
