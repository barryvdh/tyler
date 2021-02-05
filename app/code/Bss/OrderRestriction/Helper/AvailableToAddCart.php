<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Model\ResourceModel\OrderedProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class AvailableToAddCart
 * Check the availability of product with current customer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AvailableToAddCart
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var TimezoneInterface
     */
    private $localeTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var OrderedProduct
     */
    private $orderedProductResource;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * AvailableToAddCart constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param TimezoneInterface $localeTime
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     * @param OrderedProduct $orderedProductResource
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        TimezoneInterface $localeTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        ProductRepositoryInterface $productRepository,
        OrderedProduct $orderedProductResource,
        OrderRuleRepositoryInterface $orderRuleRepository,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->localeTime = $localeTime;
        $this->dateTime = $dateTime;
        $this->productRepository = $productRepository;
        $this->orderedProductResource = $orderedProductResource;
        $this->orderRuleRepository = $orderRuleRepository;
        $this->messageManager = $messageManager;
    }

    /**
     * Can add product to cart
     *
     * @param array $productIds
     * @return bool
     * @throws LocalizedException
     */
    public function canAddProductToCart($productIds)
    {
        return empty($this->getNotAllowedProductsToAdd($productIds));
    }

    /**
     * Check the available add to cart of product
     *
     * @param array $productDataIds - ["product_id" => 1, "qty" => 1, "type" => "simple"]
     * @param bool $isAddToCart
     * @return array
     * @throws LocalizedException
     */
    public function getNotAllowedProductsToAdd($productDataIds, $isAddToCart = false)
    {
        if (!$this->customerSession->isLoggedIn()) {
            return [];
        }

        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            throw new LocalizedException(__("The session not found!. Please re-login!"));
        }

        $notAllowedProducts = [];
        $storeDate = $this->localeTime->date();
        $filterDate = [
            "startDate" => $this->dateTime->date("Y-m-01", $storeDate),
            "endDate" => $this->dateTime->date("Y-m-t", $storeDate),
            "configTz" => $storeDate->format("P"),
            "defaultTz" => $storeDate->setTimezone(
                new \DateTimeZone($this->localeTime->getDefaultTimezone())
            )->format("P")
        ];

        foreach ($productDataIds as $productData) {
            $productOrderRule = $this->orderRuleRepository->getByProductId($productData["product_id"]);
            $allowedSaleQty = $productOrderRule->getSaleQtyPerMonth();

            if ($allowedSaleQty == null || $allowedSaleQty == "") {
                continue;
            }

            if ($isAddToCart) {
                $allowedSaleQty++;
            }

            $totalOrderedQty = $this->orderedProductResource->getTotalOrderedQty(
                $customerId,
                $productData["product_id"],
                $filterDate
            );

            if (((int) $totalOrderedQty + $productData["qty"]) >= $allowedSaleQty) {
                $product = $this->productRepository->getById($productData["product_id"]);
                $notAllowedProducts[$productData["product_id"]] = $product->getName();
            }
        }

        return $notAllowedProducts;
    }

    /**
     * Get restriction message
     *
     * @param array $notAllowedProducts
     * @return string
     */
    public function getRestrictionMessages($notAllowedProducts)
    {
        return $this->messageManager->addErrorMessage(
            __(
                "[%1] products that reached the order limit this month.",
                implode(", ", $notAllowedProducts)
            )
        );
    }
}
