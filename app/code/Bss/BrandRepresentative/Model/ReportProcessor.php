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
use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;

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
     * ReportProcessor constructor.
     * @param SalesReportFactory $report
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        SalesReportFactory $report,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->report = $report;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Process to Save Data Report
     *
     * @param Order $order
     * @return array
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
                /* @var Product $product */
                $product = $item->getProduct();
                if (!$product) {
                    continue;
                }
                $newReport->setProductSku($product->getSku());
                $newReport->setProductName($product->getName());
                $newReport->setProductType($product->getTypeId());
                $newReport->setOrderedQty($item->getQtyOrdered());
                $newReport->setOrderedTime($order->getCreatedAt());
                $newReport->setCustomerName($order->getCustomerName());
                $province = '';
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
                        $province = $order->getBillingAddress()->getRegionId();
                        $newReport->setProvince($province);
                        $newReport->setAddress(implode(',', $billingAddress));
                    }

                }
                $email = $this->helper->extractRepresentativeEmail(
                    $product,
                    $province
                );

                $newReport->setRepresentativeEmail($email);

                $newReport->setSentStatus(SalesReport::SENT_STATUS_NOT_SEND);

                try {
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
