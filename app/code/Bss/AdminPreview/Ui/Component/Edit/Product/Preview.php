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

namespace Bss\AdminPreview\Ui\Component\Edit\Product;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Bss\AdminPreview\Plugin\FrontendUrl;

/**
 * Class Preview
 */
class Preview extends Generic
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface
     */
    protected $context;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Preview constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FrontendUrl $frontendUrl
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FrontendUrl $frontendUrl,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Request\Http $request
    )
    {
        parent::__construct($context, $registry);
        $this->frontendUrl = $frontendUrl;
        $this->dataHelper = $dataHelper;
        $this->authorization = $authorization;
        $this->request = $request;
        $this->coreRegistry = $registry;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $storeId = $this->request->getParam('store');
        $product_id = $this->request->getParam('id');
        $product = $this->coreRegistry->registry('current_product');
        if ($this->dataHelper->isEnable() && $this->authorization->isAllowed('Bss_AdminPreview::config_section')
            && $product->getVisibility() != 1 && $product->getStatus() == 1) {
            return [
                'label' => __('Preview'),
                'on_click' => sprintf("window.open('%s')", $this->getProductUrl($product_id, $storeId)),
                'class' => '',
                'sort_order' => 10
            ];
        }
    }

    /**
     * @param $product_id
     * @param $storeId
     * @return mixed
     */
    public function getProductUrl($product_id, $storeId)
    {
        return $this->frontendUrl->getFrontendUrl()
            ->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $storeId]);
    }
}
