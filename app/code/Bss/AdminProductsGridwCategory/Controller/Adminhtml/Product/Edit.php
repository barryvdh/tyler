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
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Bss\AdminProductsGridwCategory\Controller\Adminhtml\Product;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Bss\AdminProductsGridwCategory\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;

class Edit extends Product
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;

    /**
     * @var \Bss\AdminProductsGridwCategory\Helper\Data
     */
    protected $helper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param ProductFactory $productFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Filter $filter
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param CustomerSession $customerSession
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ProductFactory $productFactory,
        ForwardFactory $resultForwardFactory,
        Filter $filter,
        CategoryLinkManagementInterface $categoryLinkManagement,
        CustomerSession $customerSession,
        Data $helper
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $productFactory, $resultForwardFactory);
        $this->filter = $filter;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    /**
     * Product view mass action change category
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->helper->getGeneralConfig('enable')) {
            $isPost = $this->getRequest()->getPost();
            $formData = $this->getRequest()->getPostValue();
            if ($isPost && !isset($formData['selected']) && !isset($formData['filters'])) {
                $this->save($formData);
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('catalog/product');
                return $resultRedirect;
            }

            $collection = $this->filter->getCollection($this->productFactory->create()->getCollection());
            $bssProductIds = [];
            foreach ($collection->getAllIds() as $productId) {
                $bssProductIds[] = $productId;
            }

            $this->customerSession->setBssProductIds($bssProductIds);
            $model = $this->productFactory->create();
            if (empty($bssProductIds)) {
                $this->messageManager->addErrorMessage(__('You must choose a item.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('catalog/product');
                return $resultRedirect;
            }

            // Restore previously entered form data from session  -  remove this code can error in grid
            $data = $this->_session->getProductData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('Category update'));
            return $resultPage;
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultRedirect;
        }
    }

    /**
     * @param $formData
     */
    protected function save($formData)
    {
        $validate = true;
        if (empty($formData['categories_id'])) {
            $this->messageManager->addErrorMessage(__('You must choose a category.'));
            $validate = false;
        }

        if (!isset($formData['ids'])) {
            $this->messageManager->addErrorMessage(__('You must choose a product.'));
            $validate = false;
        }

        if ($validate) {
            $arrIds = explode(',', $formData['ids']);
            $arrIdCollection = $this->productFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in' => $arrIds]);
            foreach ($arrIdCollection as $product) {
                $category = $product->getCategoryIds();
                $categoryNew = array_merge($category, $formData['categories_id']);
                if ($formData['is_unlink_old_category'] == '1') {
                    $categoryNew = $formData['categories_id'];
                }
                $this->categoryLinkManagement->assignProductToCategories($product->getSku(), $categoryNew);
            }
            $this->messageManager->addSuccessMessage(__('The product has been update category.'));
        }
    }

    /**
     * Product access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_AdminProductsGridwCategory::action_product');
    }
}
