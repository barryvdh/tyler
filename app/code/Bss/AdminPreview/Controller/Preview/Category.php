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

namespace Bss\AdminPreview\Controller\Preview;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Category
 * @package Bss\AdminPreview\Controller\Preview
 */
class Category extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * Category constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Catalog\Model\CategoryFactory $category
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Catalog\Model\CategoryFactory $category
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->category = $category;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $catId = $this->getRequest()->getParam('category_id');
        $storeId = $this->getRequest()->getParam('store');
        $category = $this->category->create()->load($catId);
        if ($storeId) {
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $categoryUrl = strtok($category->setStoreId($storeId)->getUrl(), '?') . '?___store=' . $storeCode;
        } else {
            $storeId = '0';
            $categoryUrl = strtok($category->setStoreId($storeId)->getUrl(), '?');
            if ($this->productMetadata->getVersion() < '2.3.0') {
                $storeCode = $this->storeManager->getStore('0')->getCode();
                $categoryUrl .= '?___store=' . $storeCode;
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($categoryUrl);
        return $resultRedirect;

    }

}
