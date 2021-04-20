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

/**
 * Adminpreview LoginAsCustomer log action
 */
class Index extends \Magento\Backend\App\Action
{

    /**
     * @var \Bss\AdminPreview\Model\LoginFactory
     */
    protected $bssLogin;

    /**
     * Index constructor.
     * @param Action\Context $context
     * @param \Bss\AdminPreview\Model\Login $bssLogin
     */
    public function __construct(Action\Context $context, \Bss\AdminPreview\Model\LoginFactory $bssLogin)
    {
        parent::__construct($context);
        $this->bssLogin = $bssLogin;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->bssLogin->create()->deleteNotUsed();

        $this->_view->loadLayout();
        $this->_setActiveMenu('Bss_AdminPreview::login_log');
        $title = __('Login As Customer Log ');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdminPreview::login_log');
    }
}
