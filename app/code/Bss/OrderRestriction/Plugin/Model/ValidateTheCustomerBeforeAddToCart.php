<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model;

use Bss\OrderRestriction\Helper\AvailableToAddCart;
use Bss\OrderRestriction\Helper\ObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class ValidateTheCustomerBeforeAddToCart
 * Check the product allowed order qty with current customer
 */
class ValidateTheCustomerBeforeAddToCart
{
    /**
     * @var string[]
     */
    private $wislistActionNames = [
        "wishlist_index_cart",
        "wishlist_index_allcart"
    ];

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
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    private $wishlistProvider;

    /**
     * ValidateTheCustomerBeforeAddToCart constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param AvailableToAddCart $addCartChecker
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ObjectHelper $objectHelper
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        AvailableToAddCart $addCartChecker,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ObjectHelper $objectHelper,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
    ) {
        $this->logger = $logger;
        $this->addCartChecker = $addCartChecker;
        $this->jsonFactory = $jsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->objectHelper = $objectHelper;
        $this->wishlistProvider = $wishlistProvider;
    }

    /**
     * Validate the customer with product before add to cart or update item qty
     *
     * @param $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     * @throws LocalizedException
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

        $requestProductData = $this->prepareRequestProductIds(
            $request->getParams(),
            $request->getParam("qty", 1)
        );

        // Wishlist to cart
        if ($this->isFromWishlist($request)) {
            $requestProductData = $this->getWishListToCartProduct($request);
        }

        // Update items qty in cart page
        // And minicart
        if ($request->has("cart") ||
            $request->getFullActionName() == "checkout_sidebar_updateItemQty"
        ) {
            $requestProductData = $this->getUpdateItemsQtyData($request);
        }

        $relatedProducts = $this->getRelatedProducts($request);
        $requestProductData = array_merge($requestProductData, $relatedProducts);

        try {
            $canAddProduct = $this->addCartChecker->canAddProductToCart($requestProductData);

            if ($canAddProduct) {
                return $proceed();
            }
            $this->addCartChecker->getRestrictionMessages();
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
     * Get product data when update qty in cart page or mini cart
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getUpdateItemsQtyData($request)
    {
        $productData = [];
        try {
            $quote = $this->getQuote();
            // Get item and qty data when update request on cart page
            $updateCartItems = $request->getParam("cart", []);

            // Get item and qty with update request on mini cart
            if ($itemId = $request->getParam("item_id")) {
                $updateCartItems[$itemId] =  ["qty" => $request->getParam("item_qty")];
            }

            foreach ($quote->getAllItems() as $item) {
                if ($item->getProductType() == "configurable") {
                    $existDataIdx = array_search(
                        $item->getProductId(),
                        array_column($productData, "product_id")
                    );
                    if ($existDataIdx !== false) {
                        $productData[$existDataIdx]["qty"] += 1;
                    } else {
                        $productData[] = [
                            "product_id" => $item->getProductId(),
                            "qty" => 1
                        ];
                    }
                    continue;
                }
                if (isset($updateCartItems[$item->getId()]) ||
                    isset($updateCartItems[$item->getParentItemId()])
                ) {
                    $qty = $updateCartItems[$item->getId()]["qty"] ?? 1;
                    if ($item->getParentItemId()) {
                        $parentQty = $updateCartItems[$item->getParentItemId()]["qty"] ?? 1;
                        $qty = $parentQty * $item->getQty();
                    }
                    $productDataItem = [
                        "product_id" => $item->getProductId(),
                        "qty" => $qty
                    ];

                    $productData[] = $productDataItem;
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $productData;
    }

    /**
     * The request is from wishlist
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return bool
     */
    private function isFromWishlist($request)
    {
        return in_array($request->getFullActionName(), $this->wislistActionNames);
    }

    /**
     * Get add to cart product from wish list
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @return array
     */
    private function getWishListToCartProduct($request)
    {
        $productData = [];
        try {
            $wishlistItemId = $request->getParam("item");
            $qty = $request->getParam("qty", 1);
            $wishListId = $request->getParam("wishlist_id");
            $wishlistItems = $this->wishlistProvider->getWishlist($wishListId)->getItemCollection();

            if ($wishlistItemId && !is_array($qty)) {
                $qty = [
                    $wishlistItemId => $qty
                ];
            }

            if (is_array($qty)) {
                foreach ($wishlistItems as $item) {
                    /** @var \Magento\Wishlist\Model\Item $item */
                    if (isset($qty[$item->getId()])) {
                        $infoByRequest = $this->objectHelper->getSerializer()->unserialize(
                            $item->getOptionByCode("info_buyRequest")->getValue()
                        );
                        $productTypeOpt = $item->getOptionByCode("product_type");

                        if ($productTypeOpt &&
                            $productTypeOpt->getValue() === "grouped"
                        ) {
                            $infoByRequest["super_group"][$item->getProductId()] = $qty[$item->getId()];
                        }

                        $productData = [
                            ...$productData,
                            ...$this->prepareRequestProductIds($infoByRequest, $qty[$item->getId()])
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $productData;
    }

    /**
     * Prepare request product data
     *
     * @param array $requestInfoData
     * @param int $qty
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareRequestProductIds($requestInfoData, $qty): array
    {
        $requestProductData = [];

        // Bundle -> oke
        if (isset($requestInfoData["bundle_option"])) {
            $requestProductData = $this->getBundleAddToCartProductData($requestInfoData, $qty);
        }

        // Grouped -> oke
        if (isset($requestInfoData["super_group"])) {
            $requestProductData = $this->getGroupedAddToCartProductData($requestInfoData);
        }

        // Configurable
        if (isset($requestInfoData["super_attribute"])) {
            $requestProductData = $this->getConfigurableAddToCartProductData($requestInfoData, $qty);
        }

        $productId = $requestInfoData["product"] ?? null;

        // Parent product id, simple, virtual, downloadable
        if ($productId) {
            $requestProductData[] = [
                "product_id" => $productId,
                "qty" => $qty
            ];
        }

        // Get qty in cart and merge them
        $cartProductQty = [];
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getProductType() === "configurable") {
                // configurable product qty should be same qty with child
                $itemQty = $item->getQty();
                if (isset($cartProductQty[$item->getProductId()])) {
                    $itemQty += $cartProductQty[$item->getProductId()];
                }

                $cartProductQty[$item->getProductId()] = $itemQty;
                continue;
            }
            $itemQty = $item->getQty();
            $parentItem = $item->getParentItem();

            if ($parentItem && $parentItem->getProductType() === "bundle") {
                $itemQty *= $parentItem->getQty();
            }
            $cartProductQty[$item->getProductId()] = $itemQty;
        }

        array_walk($requestProductData, function (&$productData) use ($cartProductQty) {
            if (isset($cartProductQty[$productData["product_id"]])) {
                $productData["qty"] += $cartProductQty[$productData["product_id"]];
            }

            return $productData;
        });

        return $requestProductData;
    }

    /**
     * Prepare configurable product data
     *
     * @param array $requestInfoData
     * @param int $qty
     * @return array
     */
    private function getConfigurableAddToCartProductData($requestInfoData, $qty)
    {
        try {
            $requestData = $requestInfoData["super_attribute"];
            $productRepository = $this->objectHelper->getProductRepository();
            $product = $productRepository->getById($requestInfoData["product"]);
            $associatedProduct = $this->objectHelper
               ->getConfigurableProductType()
               ->getProductByAttributes($requestData, $product);

            return [[
               "product_id" => $associatedProduct->getId(),
               "qty" => $qty
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
     * @param array $requestInfoData
     * @return array
     */
    private function getGroupedAddToCartProductData($requestInfoData)
    {
        $groupedChildrenProductData = [];
        try {
            $requestData = $requestInfoData["super_group"];

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
     * @param array $requestInfoData
     * @param int $qty
     * @return array
     */
    private function getBundleAddToCartProductData($requestInfoData, $qty)
    {
        try {
            $bundleOptions = $requestInfoData["bundle_option"];
            $bundleOptionQty = $requestInfoData["bundle_option_qty"] ?? [];
            $bundleProductQty = $qty;

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
                $itemData["qty"] = $bundleOptionQty[$optionId];
            }

            $data[] = $itemData;
        }

        return $data;
    }

    /**
     * Get checkout quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getQuote()
    {
        return $this->objectHelper->getCheckoutSession()->getQuote();
    }
}
