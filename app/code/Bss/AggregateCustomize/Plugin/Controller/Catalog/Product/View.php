<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Controller\Catalog\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class View
 * Check to allow can see the product by the preview
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class View
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * View constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\ForwardFactory $forwardFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->forwardFactory = $forwardFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Check to allow can see the product by the preview
     *
     * @param \Magento\Catalog\Controller\Product\View $subject
     * @return array
     */
    public function beforeExecute(
        $subject
    ) {
        try {
            $request = $subject->getRequest();
            $productId = $request->getParam('id');

            if ($productId) {
                $product = $this->productRepository->getById($productId);

                $adminPreviewAttr = $product->getCustomAttribute('bss_admin_preview');
                if ($adminPreviewAttr &&
                    (int) $adminPreviewAttr->getValue() === 1
                ) {
                    if (!$this->cookieManager->getCookie('adminLogged')) {
                        $resultForward = $this->forwardFactory->create();
                        $resultForward->forward('noroute');

                        return $resultForward;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [];
    }
}
