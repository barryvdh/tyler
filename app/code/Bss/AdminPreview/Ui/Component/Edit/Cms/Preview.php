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

namespace Bss\AdminPreview\Ui\Component\Edit\Cms;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 * Class Preview
 */
class Preview extends Generic
{

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Preview constructor.
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        parent::__construct($context, $registry);
        $this->dataHelper = $dataHelper;
        $this->authorization = $authorization;
        $this->request = $request;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->page = $page;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        $page_id = $this->request->getParam('page_id');
        $page = $this->page->load($page_id);
        if ($this->dataHelper->isEnable() && $this->authorization->isAllowed('Bss_AdminPreview::config_section') && $page && $page->isActive()) {
            return [
                'label' => __('Preview'),
                'on_click' => sprintf("window.open('%s')", $this->getCmsPageUrl($page)),
                'class' => '',
                'sort_order' => 100
            ];
        }
    }

    /**
     * @param $page
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPageUrl($page)
    {
        $store_id = $page->getStoreId()[0];
        $identifier = $page->getIdentifier();
        $url = $this->storeManagerInterface->getStore()->getBaseUrl() . $identifier;

        if ($this->productMetadata->getVersion() < '2.3.0') {
            $storeCode = $this->storeManagerInterface->getStore($store_id)->getCode();
            $url .= '?___store=' . $storeCode;
        }

        return $url;
    }
}
