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

namespace Bss\AdminPreview\Plugin\Adminhtml\Edit;

/**
 * Class ButtonListProduct
 * @package Bss\AdminPreview\Plugin\Adminhtml\Edit
 */
class ButtonListProduct extends ButtonList
{
    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit $subject
     */
    public function beforeGetBackButtonHtml(\Magento\Catalog\Block\Adminhtml\Product\Edit $subject)
    {
        if ($this->dataHelper->isEnable() && $this->authorization->isAllowed('Bss_AdminPreview::config_section')) {
            $product_id = $subject->getRequest()->getParam('id');
            $subject->getToolbar()->addChild(
                'bss_preview_product',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Preview'),
                    'class' => 'action-back',
                    'onclick' => 'window.open(\'' . $this->getProductUrl($subject, $product_id) . '\')',
                ]
            );
        }
    }

    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit $subject
     * @return mixed
     */
    public function afterGetBackButtonHtml(\Magento\Catalog\Block\Adminhtml\Product\Edit $subject)
    {
        return $subject->getToolbar()->getChildHtml('bss_preview_product');
    }

    /**
     * @param $subject
     * @param $product_id
     * @return mixed
     */
    public function getProductUrl($subject, $product_id)
    {
        $store = $subject->getRequest()->getParam('store');
        return $this->url->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $store]);
    }
}