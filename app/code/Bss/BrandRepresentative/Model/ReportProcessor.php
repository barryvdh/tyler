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

use Magento\Catalog\Model\Product;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Bss\BrandRepresentative\Helper\Data;

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
     * ReportProcessor constructor.
     * @param SalesReportFactory $report
     * @param Data $helper
     */
    public function __construct(
        SalesReportFactory $report,
        Data $helper
    ) {
        $this->report = $report;
        $this->helper = $helper;
    }

    /**
     * Process to Save Data Report
     *
     * @param Order $order
     * @return array
     */
    public function processSaveReport(Order $order): array
    {
        $returnStatus = [
            'status' => false,
            'message' => ''
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
                if ($order->getBillingAddress()) {
                    $newReport->setAddress($order->getBillingAddress()->getStreet());
                }
                if ($order->getShippingAddress()) {
                    $newReport->setAddress2($order->getShippingAddress()->getStreet());
                    $newReport->setCity($order->getShippingAddress()->getCountryId());
                    $newReport->setProvince($order->getShippingAddress()->getRegionId());
                }
                $email = $this->helper->extractRepresentativeEmail($product);

                $newReport->setSentStatus(SalesReport::SENT_STATUS_NOT_SEND);
            }

        }
    }
}
