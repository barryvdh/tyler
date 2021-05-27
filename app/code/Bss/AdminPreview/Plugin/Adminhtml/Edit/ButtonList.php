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

use Bss\AdminPreview\Plugin\FrontendUrl;

/**
 * Class ButtonList
 * @package Bss\AdminPreview\Plugin\Adminhtml\Edit
 */
class ButtonList
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Cms\Model\Page|\Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * ButtonList constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Cms\Model\Page $page
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param FrontendUrl $frontendUrl
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Cms\Model\Page $page,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FrontendUrl $frontendUrl,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        $this->authorization = $authorization;
        $this->dataHelper = $dataHelper;
        $this->page = $page;
        $this->storeManager = $storeManager;
        $this->frontendUrl = $frontendUrl;
        $this->request = $request;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Backend\Block\Widget\Context $subject
     * @param $buttonList
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        $buttonList
    )
    {
        if ($this->dataHelper->isEnable() && $this->authorization->isAllowed('Bss_AdminPreview::config_section')) {
            if ($this->request->getFullActionName() == 'cms_page_edit') {
                $page_id = $subject->getRequest()->getParam('page_id');
                if ($page_id) {
                    $buttonList->add(
                        'bss_cms_preview',
                        [
                            'label' => __('Preview'),
                            'onclick' => 'window.open(\'' . $this->getCmsPageUrl($page_id) . '\')',
                            'class' => 'ship'
                        ]
                    );
                }
            }
            if ($this->request->getFullActionName() == 'catalog_category_edit') {
                $cat_id = $subject->getRequest()->getParam('id');
                if ($cat_id) {
                    $buttonList->add(
                        'bss_category_preview',
                        [
                            'label' => __('Preview'),
                            'onclick' => 'window.open(\'' . $this->getCategoryUrl($subject, $cat_id) . '\')',
                            'class' => 'ship'
                        ]
                    );
                }
            }
        }

        return $buttonList;
    }

    /**
     * @param $page_id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPageUrl($page_id)
    {
        $page = $this->page->load($page_id);
        $store_id = $page->getStoreId()[0];
        $identifier = $page->getIdentifier();
        $url = $this->storeManager->getStore()->getBaseUrl() . $identifier;

        if ($this->productMetadata->getVersion() < '2.3.0') {
            $storeCode = $this->storeManagerInterface->getStore($store_id)->getCode();
            $url .= '?___store=' . $storeCode;
        }

        return $url;
    }

    /**
     * @param $subject
     * @param $cat_id
     * @return mixed
     */
    public function getCategoryUrl($subject, $cat_id)
    {
        $store = $subject->getRequest()->getParam('store');
        return $this->frontendUrl->getFrontendUrl()
            ->getUrl('adminpreview/preview/category', ['cat_id' => $cat_id, 'store' => $store]);
    }

}
