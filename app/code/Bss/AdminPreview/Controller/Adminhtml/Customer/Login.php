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

namespace Bss\AdminPreview\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Bss\AdminPreview\Plugin\FrontendUrl;

/**
 * LoginAsCustomer login action
 */
class Login extends Action
{

    /**
     * @var \Bss\AdminPreview\Model\LoginFactory
     */
    protected $bssLogin;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * Login constructor.
     * @param Action\Context $context
     * @param \Bss\AdminPreview\Model\LoginFactory $bssLogin
     * @param \Magento\Backend\Model\Auth\Session $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param FrontendUrl $frontendUrl
     */
    public function __construct(
        Action\Context $context,
        \Bss\AdminPreview\Model\LoginFactory $bssLogin,
        \Magento\Backend\Model\Auth\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FrontendUrl $frontendUrl
    )
    {
        parent::__construct($context);
        $this->bssLogin = $bssLogin;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $login = $this->bssLogin->create()->setCustomerId($customerId);
        $login->deleteNotUsed();
        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
        }

        $user = $this->session->getUser();
        $login->generate($user->getId());

        $store = $this->storeManager->getStore($customer->getStoreId());
        $url = $this->frontendUrl->getFrontendUrl()->setScope($store);

        $redirectUrl = $url->getUrl('adminpreview/customer/index', ['secret' => $login->getSecret(), '_nosid' => true]);
        $this->getResponse()->setRedirect($redirectUrl);
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdminPreview::login_button');
    }
}
