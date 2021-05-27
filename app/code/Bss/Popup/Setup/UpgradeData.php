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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Bss\Popup\Helper\Layout
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * UpgradeData constructor.
     * @param \Bss\Popup\Helper\Layout $helper
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Bss\Popup\Helper\Layout $helper,
        \Magento\Framework\App\State $state
    ) {
        $this->helper = $helper;
        $this->state = $state;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.1.1", "<")) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
            $this->updateData();

        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @codingStandardsIgnoreStart
     */
    public function updateData()
    {
        $collection = $this->helper->getPopupCollection();
        foreach ($collection as $item) {
            $data = [];
            $pageDisplay = $item->getPageDisplay();
            if ($pageDisplay == '') {
                continue;
            }
            $popupId = $item->getId();
            $pageDisplay = explode(",", $pageDisplay);
            $productPage = false;
            if (in_array(3, $pageDisplay)) { // product
                $data['3']['page_group'] = 'all_products';
                $data['3']['layout_handle'] = 'catalog_product_view';
                $excludeProduct = $item->getExcludeProduct();
                if ($excludeProduct != '') {
                    $productIds = $this->helper->getAllExcludeProductId(explode(",", $excludeProduct));
                    $data['3']['page_group'] = 'all_products';
                    $data['3']['layout_handle'] = 'catalog_product_view';
                    if ($productIds == '') {
                        $data['3']['page_for'] = 'all';
                        $productPage = true;
                    } else {
                        $data['3']['page_for'] = 'specific';
                    }
                    $data['3']['entities'] = $productIds;
                } else {
                    $data['3']['page_for'] = 'all';
                    $data['3']['entities'] = '';
                    $productPage = true;
                }
            }
            $categoryPage = false;
            if (in_array(2, $pageDisplay)) { // category
                $excludeCategory = $item->getExcludeCategory();
                if ($excludeCategory != '') {
                    $checkCategoryExclude = $this->helper->checkCategoryExclude(explode(",", $excludeCategory));
                    $anchor = $checkCategoryExclude['anchor'];
                    $notAnchor = $checkCategoryExclude['not_anchor'];
                    if (!empty($anchor)) {
                        $data['2-anchor']['page_group'] = 'anchor_categories';
                        $data['2-anchor']['layout_handle'] = 'catalog_category_view_type_layered';
                        $data['2-anchor']['page_for'] = 'specific';
                        $data['2-anchor']['entities'] = implode(",", $anchor);
                    } else {
                        $data['2-anchor']['page_group'] = 'anchor_categories';
                        $data['2-anchor']['layout_handle'] = 'catalog_category_view_type_layered';
                        $data['2-anchor']['page_for'] = 'all';
                        $data['2-anchor']['entities'] = '';
                    }
                    if (!empty($notAnchor)) {
                        $data['2-notanchor']['page_group'] = 'notanchor_categories';
                        $data['2-notanchor']['layout_handle'] = 'catalog_category_view_type_default';
                        $data['2-notanchor']['page_for'] = 'specific';
                        $data['2-notanchor']['entities'] = implode(",", $notAnchor);
                    } else {
                        $data['2-notanchor']['page_group'] = 'notanchor_categories';
                        $data['2-notanchor']['layout_handle'] = 'catalog_category_view_type_default';
                        $data['2-notanchor']['page_for'] = 'all';
                        $data['2-notanchor']['entities'] = '';
                    }
                    if (empty($anchor) && empty($notAnchor)) {
                        $categoryPage = true;
                    }
                } else {
                    $data['2-anchor']['page_group'] = 'anchor_categories';
                    $data['2-anchor']['layout_handle'] = 'catalog_category_view_type_layered';
                    $data['2-anchor']['page_for'] = 'all';
                    $data['2-anchor']['entities'] = '';

                    $data['2-notanchor']['page_group'] = 'notanchor_categories';
                    $data['2-notanchor']['layout_handle'] = 'catalog_category_view_type_default';
                    $data['2-notanchor']['page_for'] = 'all';
                    $data['2-notanchor']['entities'] = '';
                    $categoryPage = true;
                }
            }
            if (in_array(6, $pageDisplay)) { // all page
                $data['6']['page_group'] = 'all_pages';
                $data['6']['layout_handle'] = 'default';
                $data['6']['page_for'] = 'all';
                $entities = [];
                if ($this->getSize($pageDisplay) == 1) {
                    $data['6']['entities'] = '';
                } else {
                    if (!in_array(1, $pageDisplay)) { // home page
                        $entities[] = 'cms_index_index';
                    }
                    if (in_array(3, $pageDisplay) && $categoryPage) { // category
                        unset($data['2-anchor']);
                        unset($data['2-notanchor']);
                    } else {
                        $entities[] = 'catalog_category_view';
                    }
                    if (in_array(3, $pageDisplay) && $productPage) { // product
                        unset($data['3']);
                    } else {
                        $entities[] = 'catalog_product_view';
                    }
                    if (!in_array(4, $pageDisplay)) { // cart page
                        $entities[] = 'checkout_cart_index';
                    }
                    if (!in_array(5, $pageDisplay)) { // checkout page
                        $entities[] = 'checkout_index_index';
                    }
                    $data['6']['entities'] = implode(",", $entities);
                }
            } else {
                if (in_array(1, $pageDisplay)) { // home page
                    $data['1']['page_group'] = 'pages';
                    $data['1']['layout_handle'] = 'cms_index_index';
                    $data['1']['page_for'] = 'all';
                    $data['1']['entities'] = '';
                }
                if (in_array(4, $pageDisplay)) { // cart page
                    $data['4']['page_group'] = 'pages';
                    $data['4']['layout_handle'] = 'checkout_cart_index';
                    $data['4']['page_for'] = 'all';
                    $data['4']['entities'] = '';
                }
                if (in_array(5, $pageDisplay)) { // checkout page
                    $data['5']['page_group'] = 'pages';
                    $data['5']['layout_handle'] = 'checkout_index_index';
                    $data['5']['page_for'] = 'all';
                    $data['6']['entities'] = '';
                }
            }
            $this->helper->updateDataToDb($popupId, $data);
        }
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param $data
     * @return int|void
     */
    protected function getSize($data)
    {
        return count($data);
    }
}
