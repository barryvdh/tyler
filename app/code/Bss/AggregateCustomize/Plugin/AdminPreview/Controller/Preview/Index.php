<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\AdminPreview\Controller\Preview;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Index
 * rewrite preview controller
 */
class Index
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;
    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * Index constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        RedirectFactory $redirectFactory
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Preview redirect
     *
     * @param \Bss\AdminPreview\Controller\Preview\Index $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Bss\AdminPreview\Controller\Preview\Index $subject,
        callable $proceed
    ) {
        $productId = $subject->getRequest()->getParam('product_id');
        $storeId = $subject->getRequest()->getParam('store');
        $product = $this->productRepository->getById($productId);

        if ($storeId) {
            $storeCode = $this->storeManager->getStore($storeId)->getCode();
            $productUrl = strtok($product->setStoreId($storeId)->getUrlInStore(), '?') . '?___store=' . $storeCode;
        } else {
            $storeId = $this->storeManager->getStore()->getId();
            $productUrl = strtok($product->setStoreId($storeId)->getUrlInStore(), '?');
            if ($this->productMetadata->getVersion() < '2.3.0') {
                $storeCode = $this->storeManager->getStore('0')->getCode();
                $productUrl .= '?___store=' . $storeCode;
            }
        }

        return $this->redirectFactory->create()->setUrl($productUrl);
    }
}
