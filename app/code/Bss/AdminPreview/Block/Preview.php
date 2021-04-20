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

namespace Bss\AdminPreview\Block;

class Preview extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $page;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Preview constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Cms\Model\Page $page
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Cms\Model\Page $page,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->_coreRegistry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->_cookieManager = $cookieManager;
        $this->backendHelper = $backendHelper;
        $this->page = $page;
        $this->_dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @return array|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getEditLink()
    {
        if ($this->_dataHelper->isEnable()) {
            $adminLogged = $this->_cookieManager->getCookie('adminLogged');
            if ($this->request->getFullActionName() == 'catalog_product_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('product')) {
                $product = $this->_coreRegistry->registry('current_product');
                $product_id = $product->getId();
                $storeId = $this->storeManager->getStore()->getId();
                $url = $this->backendHelper->getUrl('catalog/product/edit', ['id' => $product_id, 'store' => $storeId]);
                $type = 'product';
            }
            if ($this->request->getFullActionName() == 'catalog_category_view' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('category')) {
                $category = $this->_coreRegistry->registry('current_category');
                $category_id = $category->getId();
                $storeId = $this->storeManager->getStore()->getId();
                $url = $this->backendHelper->getUrl('adminpreview/edit/redirect', ['type' => 'category', 'category_id' => $category_id, 'storeId' => $storeId]);
                $type = 'category';
            }
            $whitelist_action = ['sales_order_history', 'downloadable_customer_products', 'newsletter_manage_index',
                'vault_cards_listaction', 'review_customer_index', 'paypal_billing_agreement_index', 'wishlist_index_index'];
            if (($this->request->getModuleName() == 'customer' || in_array($this->request->getFullActionName(), $whitelist_action)) &&
                $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('customer')) {
                if ($this->customerSession->isLoggedIn()) {
                    $customerId = $this->customerSession->getId();
                    $url = $this->backendHelper->getUrl('adminpreview/edit/redirect', ['type' => 'customer', 'id' => $customerId]);
                    $type = 'customer';
                }
            }
            if ($this->request->getModuleName() == 'cms' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('cms')) {
                $pageId = $this->page->getId();
                $url = $this->backendHelper->getUrl('adminpreview/edit/redirect', ['type' => 'cms_page', 'page_id' => $pageId]);
                $type = 'cms';
            }
            if (isset($url) && $url && isset($type) && $type) {
                $link = [];
                $link['url'] = $url;
                $link['type'] = $type;
                return $link;
            } else {
                return;
            }

        }
    }

}
