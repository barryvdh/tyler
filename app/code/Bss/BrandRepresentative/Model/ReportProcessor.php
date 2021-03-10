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
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class ReportProcessor
 * Bss\BrandRepresentative\Model
 */
class ReportProcessor
{
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
     */
    public function __construct(
        SalesReportFactory $report,
        Data $helper,
        LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        TimezoneInterface $localeDate
    ) {
        $this->report = $report;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->localeDate = $localeDate;
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
            $orderId = $order->getId();
            $orderItems = $order->getAllVisibleItems();
            foreach ($orderItems as $item) {
                /* @var Item $item */
                /* @var SalesReport $newReport*/
                $newReport = $this->report->create();
                $newReport->setOrderId($orderId);
                $newReport->setStoreId($order->getStoreId());

                /* @var Product $product */
                $product = $item->getProduct();
                if (!$product) {
                    continue;
                }

                //Ignore Report for downloadable and virtual product
                $productType = $product->getTypeId();
                if ($productType === 'downloadable' ||
                    $productType === 'virtual'
                ) {
                    continue;
                }

                $newReport->setProductSku($product->getSku());
                $newReport->setProductId($product->getId());
                $newReport->setProductName($product->getName());
                $newReport->setProductType($product->getTypeId());
                $newReport->setOrderedQty($item->getQtyOrdered());
                try {
                    $orderDate = $this->localeDate->date(new \DateTime($order->getCreatedAt()))->format("Y-m-d");
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    $orderDate = $this->localeDate->date()->format("Y-m-d");
                }
                $newReport->setOrderedTime($orderDate);
                $newReport->setCustomerName($order->getCustomerName());

                if ($shippingAddress = $order->getShippingAddress()) {
                    $shippingAddressStreet = $shippingAddress->getStreet();

                    if ($shippingAddressStreet) {
                        $newReport->setAddress(implode(',', $shippingAddressStreet));
                    }
                    $newReport->setCity($shippingAddress->getCity());
                    $newReport->setProvince($shippingAddress->getRegion());
                }
                $provinceId = $order->getShippingAddress()->getRegionId();
                $email = $this->helper->extractRepresentativeEmail(
                    $product,
                    $provinceId
                );
                $newReport->setRepresentativeEmail($email);
                $newReport->setSentStatus(SalesReport::SENT_STATUS_NOT_SEND);
                try {
                    /* @var Category $brand */
                    $brand = $this->categoryRepository->get(implode(',', $product->getCategoryIds()));
                    $newReport->setBrand($brand->getName());
                    $newReport->save();
                    $returnData = [
                        'success' => true
                    ];
                } catch (Exception $e) {
                    $this->logger->critical($e->getMessage());
                    $returnData = [
                        'success' => false
                    ];
                }
            }
        }

        return $returnData;
    }
}
