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

namespace Bss\AdminPreview\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

/**
 * LoginAsCustomer login action
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\AdminPreview\Model\LoginFactory
     */
    protected $bssLogin;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Bss\AdminPreview\Model\LoginFactory $bssLogin
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param PhpCookieManager $cookieManager
     */
    public function __construct(
        Context $context,
        \Bss\AdminPreview\Model\LoginFactory $bssLogin,
        \Magento\Customer\Model\Session $customerSession,
        CookieMetadataFactory $cookieMetadataFactory,
        PhpCookieManager $cookieManager
    ) {
        parent::__construct($context);
        $this->bssLogin = $bssLogin;
        $this->customerSession = $customerSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $login = $this->_initLogin();
        if (!$login) {
            $this->_redirect('/');
            return;
        }

        $isLogIn = $this->customerSession->isLoggedIn();
        if ($isLogIn) {
            $this->customerSession->logout();
        }

        try {
            $login->authenticateCustomer();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $this->messageManager->addSuccess(
            __('You are logged in as customer: %1', $login->getCustomer()->getName())
        );

        $this->_redirect('*/*/proceed');

        /** Refresh cookie */
        $metadata = $this->cookieMetadataFactory->createCookieMetadata();
        $metadata->setPath('/');
        $this->cookieManager->deleteCookie('mage-cache-sessid', $metadata);
    }

    /**
     * @return bool|\Bss\AdminPreview\Model\Login
     */
    protected function _initLogin()
    {
        $secret = $this->getRequest()->getParam('secret');
        if (!$secret) {
            $this->messageManager->addError(__('Cannot login to account. No secret key provided.'));
            return false;
        }

        $login = $this->bssLogin->create()->loadNotUsed($secret);

        if ($login->getId()) {
            return $login;
        } else {
            $this->messageManager->addError(__('Cannot login to account. Secret key is not valid.'));
            return false;
        }
    }
}
