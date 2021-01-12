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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;

use Magento\Backend\App\Action;
use Magento\Customer\Model\Attribute;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Edit
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class Edit extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Edit
{

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Attribute $model
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Attribute $model,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context, $resultPageFactory,$model, $productUrl, $eavEntity, $coreRegistry);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_entityTypeId = $this->eavEntity
            ->setType('customer_address')->getTypeId();
        return \Magento\Backend\App\Action::dispatch($request);
    }

    /**
     * Init actions
     *
     * @return \Magento\Framework\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Bss_CustomerAttributes::customer_attributes')

            ->addBreadcrumb(__('Customer Address Attributes'), __('Customer Address Attributes'))

            ->addBreadcrumb(__('Manage Customer Address Attributes'), __('Manage Customer Address Attributes'));

        return $resultPage;
    }

    /**
     * Edit Page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */

    public function execute()
    {
        $attrId = $this->editPage();
        $item = $this->getItem($attrId);
        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()
            ->prepend($attrId ? $this->model->getName() : __('New Customer Address Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js');
        return $resultPage;
    }

    /**
     * Get Item
     *
     * @param int $attrId
     * @return \Magento\Framework\Phrase
     */
    private function getItem($attrId)
    {
        if ($attrId) {
            return __('Edit Customer Address Attribute');
        } else {
            return __('New Customer Address Attribute');
        }
    }

    /**
     * Prepare Tittle
     *
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Customer'), __('Customer'))
            ->addBreadcrumb(__('Manage Customer Address Attributes'), __('Manage Customer Address Attributes'))
            ->setActiveMenu('Magento_Customer::customer');
        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Customer Address Attributes'));
        return $resultPage;
    }

    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::customer_attributes_edit');
    }
}
