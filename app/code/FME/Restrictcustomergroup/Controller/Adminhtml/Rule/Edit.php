<?php
/**
 * Class for Restrictcustomergroup Edit
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {

        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_Restrictcustomergroup::save');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('FME_Restrictcustomergroup::restrictcustomergroup')
                ->addBreadcrumb(__('FME'), __('FME'))
                ->addBreadcrumb(__('Manage Rule'), __('Manage Rule'));
        return $resultPage;
    }

    /**
     * Edit Restrictcustomergroup Index
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
      // 1. Get ID and create model
      $id = $this->getRequest()
              ->getParam('rule_id');
      $model = $this->_objectManager->create('FME\Restrictcustomergroup\Model\Rule');
      // 2. Initial checking
      if ($id)
      {
        $model->load($id);
        if (!$model->getId())
        {
            $this->messageManager->addError(__('This record no longer exists.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/*/');
        }
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
      }
      $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
      $this->_coreRegistry->register('restrictcustomergroup_data', $model);
      $this->_initAction();
      $this->_view->getLayout()
          ->getBlock('restrictcustomergroup_rule_edit')
          ->setData('action', $this->getUrl('restrictcustomergroup/*/save'));
      $this->_addBreadcrumb(
          $id ? __('Edit Rule') : __('New Rule'),
          $id ? __('Edit Rule') : __('New Rule')
      );
      $this->_view->getPage()->getConfig()->getTitle()->prepend(
          $model->getRuleId() ? $model->getTitle() : __('New Rule')
      );
      $this->_view->renderLayout();
    }
}
