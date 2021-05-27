<?php
/**
 * Class for Restrictcustomergroup Index
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_Restrictcustomergroup::rule');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
      /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
      $resultPage = $this->resultPageFactory->create();
      $resultPage->setActiveMenu('FME_Restrictcustomergroup::rule');
      $resultPage->addBreadcrumb(__('Rule'), __('Rule'));
      $resultPage->addBreadcrumb(__('Manage Rules'), __('Manage Rules'));
      $resultPage->getConfig()->getTitle()->prepend(__('Restrict Customer Group'));
      return $resultPage;
    }
}
