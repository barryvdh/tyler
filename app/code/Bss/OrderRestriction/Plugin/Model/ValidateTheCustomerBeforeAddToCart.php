<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model;

use Bss\OrderRestriction\Helper\AvailableToAddCart;
use Bss\OrderRestriction\Helper\ObjectHelper;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ValidateTheCustomerBeforeAddToCart
 * Check the product allowed order qty with current customer
 */
class ValidateTheCustomerBeforeAddToCart
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var AvailableToAddCart
     */
    private $addCartChecker;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ObjectHelper
     */
    private $objectHelper;

    /**
     * ValidateTheCustomerBeforeAddToCart constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param AvailableToAddCart $addCartChecker
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ObjectHelper $objectHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        AvailableToAddCart $addCartChecker,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ObjectHelper $objectHelper
    ) {
        $this->logger = $logger;
        $this->addCartChecker = $addCartChecker;
        $this->jsonFactory = $jsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->objectHelper = $objectHelper;
    }

    /**
     * Validate the customer with product before add to cart or update item qty
     *
     * @param $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(
        $subject,
        callable $proceed
    ) {
        if (!$this->objectHelper->getConfigProvider()->isEnabled()) {
            return $proceed();
        }
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $subject->getRequest();
//        dd($request->getParams());
        $productId = $request->getParam("product");

        if ($request->getParam("bundle_option")) {
            $requestProductData = $this->getBundleAddToCartProductData($request);
        }

        if ($request->has("super_group")) {
            $requestProductData = $this->getGroupedAddToCartProductData($request);
        }

        if ($request->has("super_attribute")) {
            $requestProductData = $this->getConfigurableAddToCartProductData($request);
        }

        $requestProductData[] = [
            "product_id" => $productId,
            "qty" => $request->getParam("qty", 1)
        ];

        $relatedProducts = $this->getRelatedProducts($request);
        $requestProductData = [...$requestProductData, ...$relatedProducts];

        try {
            $notAllowedProducts = $this->addCartChecker->getNotAllowedProductsToAdd(
                $requestProductData,
                true
            );

            if (empty($notAllowedProducts)) {
                return $proceed();
            }
            $this->addCartChecker->getRestrictionMessages($notAllowedProducts);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        if ($subject->getRequest()->getModuleName() === "wishlist" ||
            !$subject->getRequest()->isAjax()
        ) {
            return $this->redirectFactory->create()->setPath('*/*');
        }

        return $this->jsonFactory->create()->setData([
            'bss_is_restricted' => true
        ]);
    }

    /**
     * Prepare configurable product data
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getConfigurableAddToCartProductData($request)
    {
        try {
            $requestData = $request->getParam("super_attribute");
            $productRepository = $this->objectHelper->getProductRepository();
            $product = $productRepository->getById($request->getParam("product"));
            $associatedProduct = $this->objectHelper
               ->getConfigurableProductType()
               ->getProductByAttributes($requestData, $product);

            return [[
               "product_id" => $associatedProduct->getId(),
               "qty" => $request->getParam("qty", 1)
            ]];
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [];
    }

    /**
     * Prepare related products data
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getRelatedProducts($request)
    {
        $related = $request->getParam("related_product");

        if (empty($related)) {
            return [];
        }
        $relatedProducts = [];
        $related = explode(",", $related);
        foreach ($related as $productId) {
            $productData = [
                "product_id" => $productId,
                "qty" => 1
            ];

            $relatedProducts[] = $productData;
        }

        return $relatedProducts;
    }

    /**
     * Prepare grouped product date
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getGroupedAddToCartProductData($request)
    {
        $groupedChildrenProductData = [];
        try {
            $requestData = $request->getParam("super_group");

            foreach ($requestData as $productId => $qty) {
                $childData = [
                    "product_id" => $productId,
                    "qty" => $qty
                ];

                $groupedChildrenProductData[] = $childData;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $groupedChildrenProductData;
    }

    /**
     * Prepare bundle product data
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getBundleAddToCartProductData($request)
    {
        try {
            $bundleOptions = $request->getParam("bundle_option");
            $bundleOptionQty = $request->getParam("bundle_option_qty");
            $bundleProductQty = $request->getParam("qty", 1);

            $bundleOptionsData = $this->getSelectionDefaultValue($bundleOptions, $bundleOptionQty);

            array_walk($bundleOptionsData, function (&$itemData) use ($bundleProductQty) {
                $itemData["qty"] *= $bundleProductQty;

                return $itemData;
            });

            return $bundleOptionsData;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return [];
        }
    }

    /**
     * Get bundle product child data
     *
     * @param array $bundleOptions
     * @param array $bundleOptionQty - user defined qty
     * @return array
     */
    private function getSelectionDefaultValue($bundleOptions, $bundleOptionQty)
    {
        $data = [];
        foreach ($bundleOptions as $optionId => $selection) {
            if (is_array($selection)) {
                $data = [...$data, ...$this->getSelectionDefaultValue($selection, $bundleOptionQty)];

                continue;
            }

            $itemData = $this->objectHelper->getBundleProductResource()->getSelectionDefaultValue($selection);

            if (isset($bundleOptionQty[$optionId])) {
                $itemData["qty"] = $bundleOptionQty["$optionId"];
            }

            $data[] = $itemData;
        }

        return $data;
    }
}
