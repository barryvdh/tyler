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
namespace Addify\PasswordProtected\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) 
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();


        $resultPage->setActiveMenu('Addify_PasswordProtected::managepasswordprotected');
        $resultPage->addBreadcrumb(__('Addify'), __('Addify'));
        $resultPage->addBreadcrumb(__('Manage Password Protected'), __('Manage Password Protected'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Password Protected'));
        return $resultPage;
    }


 
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Addify_PasswordProtected::managepasswordprotected');

    }
}