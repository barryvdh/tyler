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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;


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
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * ReportProcessor constructor.
     * @param SalesReportFactory $report
     * @param Data $helper
     * @param LoggerInterface $logger
     * @param DateTime $dateTime
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        SalesReportFactory $report,
        Data $helper,
        LoggerInterface $logger,
        DateTime $dateTime,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->report = $report;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->dateTime = $dateTime;
        $this->categoryRepository = $categoryRepository;
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
                $newReport->setOrderedTime($this->dateTime->gmtDate('y-m-d', $order->getCreatedAt()));
                $newReport->setCustomerName($order->getCustomerName());
                if ($order->getShippingAddress()) {
                    $shippingAddress = $order->getShippingAddress()->getStreet();
                    if ($shippingAddress) {
                        $newReport->setAddress(implode(',', $shippingAddress));
                    }
                }
                if ($order->getBillingAddress()) {
                    $billingAddress = $order->getBillingAddress()->getStreet();
                    if ($billingAddress) {
                        $newReport->setCity($order->getBillingAddress()->getCity());
                        $province = $order->getBillingAddress()->getRegion();
                        $newReport->setProvince($province);
                        $newReport->setAddress(implode(',', $billingAddress));
                    }
                }
                $provinceId = $order->getBillingAddress()->getRegionId();
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
