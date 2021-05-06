<?php
/**
 * Password Protected
 *
 * @category    Addify
 * @package     Addify_PasswordProtected
 * @author      Addify
 * @Email       addifypro@gmail.com
 *
 */
namespace Addify\PasswordProtected\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->coreSession = $coreSession;

        parent::__construct($context);
    }
	public function execute()
    {
        $this->coreSession->start();
        $redirectData =$this->coreSession->getPasswordProductRedirect();
        if(!$redirectData):
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('customer/account');
        endif;
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
