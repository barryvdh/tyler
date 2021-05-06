<?php
/**
 * Class for Restrictcustomergroup MassDisable
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use FME\Restrictcustomergroup\Model\ResourceModel\Rule\CollectionFactory;

/**
 * Class MassDisable
 */
class MassDisable extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(Context $context, Filter $filter, CollectionFactory $collectionFactory)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
      $collection = $this->filter->getCollection($this->collectionFactory->create());
      foreach ($collection as $item) {
          $item->setIsActive(false);
          $item->save();
      }
      $this->messageManager->addSuccess(__('A total of %1 record(s) have been disabled.', $collection->getSize()));
      /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
      $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
      return $resultRedirect->setPath('*/*/');
    }
}
