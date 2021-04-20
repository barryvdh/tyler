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

namespace Bss\AdminPreview\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class LayoutLoadBefore
 * @package Bss\AdminPreview\Observer
 */
class LayoutLoadBefore implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $_dataHelper;

    /**
     * LayoutLoadBefore constructor.
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request
    )
    {
        $this->request = $request;
        $this->_cookieManager = $cookieManager;
        $this->backendHelper = $backendHelper;
        $this->_dataHelper = $dataHelper;
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(Observer $observer)
    {
        $layout = $observer->getData('layout');
        if ($this->_dataHelper->isEnable()) {
            $adminLogged = $this->_cookieManager->getCookie('adminLogged');
            if ($adminLogged == '1') {
                if ($this->request->getFullActionName() == 'catalog_product_view'
                    && $this->_dataHelper->showLinkFrontend('product')) {
                    $layout->getUpdate()->addHandle('bss_adminpreview_editlink');
                }
                if ($this->request->getFullActionName() == 'catalog_category_view'
                    && $this->_dataHelper->showLinkFrontend('category')) {
                    $layout->getUpdate()->addHandle('bss_adminpreview_editlink');
                }
            }

            $whitelist_action = ['sales_order_history', 'downloadable_customer_products', 'newsletter_manage_index',
                'vault_cards_listaction', 'review_customer_index', 'paypal_billing_agreement_index', 'wishlist_index_index'];
            if (($this->request->getModuleName() == 'customer' ||
                    in_array($this->request->getFullActionName(), $whitelist_action)) &&
                $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('customer')) {
                if ($this->customerSession->isLoggedIn()) {
                    $layout->getUpdate()->addHandle('bss_adminpreview_editlink');
                }
            }
            if ($this->request->getModuleName() == 'cms' && $adminLogged == '1' && $this->_dataHelper->showLinkFrontend('cms')) {
                $layout->getUpdate()->addHandle('bss_adminpreview_editlink');
            }
        }
    }

}