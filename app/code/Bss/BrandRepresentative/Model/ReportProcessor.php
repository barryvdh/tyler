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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandRepresentative\Model;

use Bss\BrandRepresentative\Helper\Data;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

/**
 * Class ReportProcessor
 * Bss\BrandRepresentative\Model
 * @SuppressWarnings(CyclomaticComplexity)
 */
class ReportProcessor
{
    const BRAND_LV = 3;

    /**
     * @var SalesReportFactory
     */
    protected $report;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * ReportProcessor constructor.
     *
     * @param SalesReportFactory $report
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param CategoryRepositoryInterface $categoryRepository
     * @param TimezoneInterface $localeDate
     * @param ProductRepositoryInterface $productRepository
     * @param SerializerInterface $serializer
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        SalesReportFactory $report,
        Data $helper,
        LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        TimezoneInterface $localeDate,
        ProductRepositoryInterface $productRepository,
        SerializerInterface $serializer,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->report = $report;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->localeDate = $localeDate;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Process to Save Data Report
     *
     * @param Order $order
     * @return array
     * @suppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function processSaveReport(Order $order): array
    {
        $returnData = [
            'success' => false
        ];
        if ($order !== null && $order->getId()) {
            $reportData = [];
            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $item) {
                /* @var Item $item */
                /* @var Product $product */
                $product = $item->getProduct();
                if (!$product) {
                    continue;
                }
                //Ignore Report for downloadable and virtual product
                $productType = $product->getTypeId();
                if ($productType === 'virtual') {
                    continue;
                }
                // GROUPED - Process grouped product
                if ($item->getProductType() === \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
                    $this->processGroupedProductInfo($reportData, $item);
                    continue;
                }

                try {
                    // SIMPLE - DOWNLOADABLE - CONFIGURABLE ITEM - BUNDLE
                    if ($rpProductData = $this->getProductReportData($item)) {
                        $reportData[$item->getProductId()] = $rpProductData;
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }

            try {
                foreach ($reportData as &$data) {
                    if (isset($data['product_options']) && is_array($data['product_options'])) {
                        $data['product_options'] = $this->serializer->serialize(
                            $data['product_options']
                        );
                    }
                    /* @var SalesReport $newReport*/
                    $newReport = $this->report->create();
                    $newReport->setData($data);
                    $newReport->save();
                    $returnData['success'] = true;
                }
            } catch (\Exception $e) {
                $this->logger->critical(
                    __("Can not create report data: ") .
                    $e
                );
            }
        }

        return $returnData;
    }

    /**
     * Get report product data
     *
     * @param Item $item
     * @param Product|null $product
     * @param bool $noSetOrderQty
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductReportData(
        \Magento\Sales\Model\Order\Item $item,
        \Magento\Catalog\Api\Data\ProductInterface $product = null,
        bool $noSetOrderQty = false
    ): array {
        $order = $item->getOrder();
        if (!$product) {
            $product = $item->getProduct();
        }
        $rpProductData = [];
        $rpProductData['store_id'] = $order->getStoreId();
        $rpProductData['order_id'] = $order->getId();
        $rpProductData['product_id'] = $product->getId();
        $rpProductData['product_sku'] = $product->getSku();
        $rpProductData['product_name'] = $product->getName();
        $rpProductData['product_type'] = $product->getTypeId();
        $rpProductData['ordered_qty'] = $noSetOrderQty ? null : $item->getQtyOrdered();
        try {
            $orderDate = $this->localeDate->date(new \DateTime($order->getCreatedAt()))->format("Y-m-d");
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $orderDate = $this->localeDate->date()->format("Y-m-d");
        }
        $rpProductData['ordered_time'] = $orderDate;
        $rpProductData['customer_name'] = $order->getCustomerName();
        if ($customerId = $order->getCustomerId()) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                if ($caAttr = $customer->getCustomAttribute('ca_company_name')) {
                    $caAttr = $caAttr->getValue();
                }
                $rpProductData['company_name'] = $caAttr ?? null;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        $provinceId = 0;
        $this->setAddressInfo($rpProductData, $order, $provinceId);
        $emails = $this->helper->extractRepresentativeEmail(
            $product,
            $provinceId ?? 0
        );
        $rpProductData['representative_email'] = $emails;
        $this->processBrandInformation($rpProductData, $product);

        return $rpProductData;
    }

    /**
     * Process address info
     *
     * @param array $rpProductData
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param int $provinceId
     */
    protected function setAddressInfo(
        array &$rpProductData,
        \Magento\Sales\Api\Data\OrderInterface $order,
        int &$provinceId
    ) {
        $address = $order->getBillingAddress();

        if ($order->getShippingAddress()) {
            $address = $order->getShippingAddress();
        }

        $shippingAddressStreet = $address->getStreet();

        if ($shippingAddressStreet && is_array($shippingAddressStreet)) {
            $rpProductData['address'] = implode(',', $shippingAddressStreet);
        }

        $rpProductData['city'] = $address->getCity();
        $rpProductData['province'] = $address->getRegion();
        $provinceId = (int) $address->getRegionId();
    }

    /**
     * Process grouped product
     *
     * @param array $reportData
     * @param Item $item
     */
    protected function processGroupedProductInfo(
        array &$reportData,
        \Magento\Sales\Model\Order\Item $item
    ) {
        $productOptions = $item->getProductOptions();
        if (isset($productOptions['super_product_config']['product_type']) &&
            $productOptions['super_product_config']['product_type'] == GroupedType::TYPE_CODE &&
            isset($productOptions['super_product_config']['product_id'])
        ) {
            $groupedProductId = $productOptions['super_product_config']['product_id'];
            try {
                $product = $this->productRepository->getById($groupedProductId);

                $rpProductDt = $this->getProductReportData($item, $product, true);
                if (!isset($reportData[$product->getId()]) && $rpProductDt) {
                    $reportData[$product->getId()] = $rpProductDt;
                }

                $childProduct = $item->getProduct();
                if (!$childProduct || !$childProduct->getId()) {
                    return;
                }
                $rpProduct = &$reportData[$product->getId()];
                $rpProduct['ordered_qty'] += $item->getQtyOrdered();
                $rpProduct['product_options'][] = [
                    'id' => $childProduct->getId(),
                    'sku' => $childProduct->getSku(),
                    'name' => $childProduct->getName(),
                    'ordered_qty' => (int) $item->getQtyOrdered()
                ];
            } catch (\Exception $e) {
                $this->logger->critical("Error when process grouped product: " . $e);
            }
        }
    }

    /**
     * Set brand information
     *
     * @param array $rpProductData
     * @param Product $product
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function processBrandInformation(array &$rpProductData, Product $product)
    {
        $categoryIds = $product->getCategoryIds();

        foreach ($categoryIds as $categoryId) {
            $this->setBrandInformationRecursive($rpProductData, $categoryId);
        }
    }

    /**
     * Recursive set brand information to report opbject
     *
     * @param array $rpProductData
     * @param int $categoryId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function setBrandInformationRecursive(array &$rpProductData, $categoryId)
    {
        if (!$categoryId) {
            return;
        }
        $category = $this->categoryRepository->get($categoryId);
        if ((int) $category->getLevel() === static::BRAND_LV) {
            $rpProductData['brand_id'] = $category->getId();
            $rpProductData['brand_name'] = $category->getName();
        }

        if ($category->getLevel() > static::BRAND_LV) {
            $this->setBrandInformationRecursive($rpProductData, $category->getParentId());
            $categoryName = "";
            if (isset($rpProductData['category_name']) && $rpProductData['category_name']) {
                $categoryName = $rpProductData['category_name'] . ", ";
            }
            $rpProductData['category_name'] = $categoryName . $category->getName();
        }
    }
}
