<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model\Customer;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Exception\CouldNotLoadException;
use Bss\OrderRestriction\Model\ResourceModel\SalesOrder;
use Magento\Backend\Model\Dashboard\Chart\Date;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as BePlugged;
use Bss\OrderRestriction\Helper\OrderRuleValidation;

/**
 * Plugin to get the order restriction info to current customer
 */
class DataProviderWithDefaultAddresses
{
    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Date
     */
    private $dateRetriever;

    /**
     * @var SalesOrder
     */
    private $salesOrderResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var OrderRuleValidation
     */
    private $orderRuleValidation;

    // @codingStandardsIgnoreLine
    public function __construct(
        OrderRuleRepositoryInterface $orderRuleRepository,
        \Psr\Log\LoggerInterface $logger,
        Date $dateRetriever,
        SalesOrder $salesOrderResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        OrderRuleValidation $orderRuleValidation
    ) {

        $this->orderRuleRepository = $orderRuleRepository;
        $this->logger = $logger;
        $this->dateRetriever = $dateRetriever;
        $this->salesOrderResource = $salesOrderResource;
        $this->date = $date;
        $this->orderRuleValidation = $orderRuleValidation;
    }
    /**
     * Get the order restriction info
     *
     * @param BePlugged $subject
     * @param array $loadedData
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        BePlugged $subject,
        $loadedData
    ) {
        foreach ($loadedData as &$customerData) {
            try {
                $orderRule = $this->orderRuleRepository->getByCustomerId($customerData['customer']['entity_id']);
            } catch (CouldNotLoadException $e) {
                continue;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                continue;
            }

            if ($orderRule->getCustomerId()) {
                $customerData['order_restriction'] = $orderRule->getData();
            }

            $ordersReport = $this->getOrdersReportData($customerData['customer']['entity_id']);

            if ($ordersReport) {
                $customerData['order_restriction']['sales_order_report'] = $ordersReport;

                $usedOrder = $this->orderRuleValidation->getOrderCount($customerData['customer']['entity_id']);
                $remain = 0;
                if ($usedOrder < $orderRule->getOrdersPerMonth()) {
                    $remain = $orderRule->getOrdersPerMonth() - $usedOrder;
                }
                $customerData['order_restriction']['order_remain'] = [
                    'total' => $orderRule->getOrdersPerMonth(),
                    'used' => $usedOrder,
                    'remain' => $remain
                ];
            }
        }

        return $loadedData;
    }

    /**
     * Get orders report data and format it
     *
     * @param int $customerId
     * @return array
     */
    private function getOrdersReportData($customerId)
    {
        $data = [];
        $dates = $this->dateRetriever->getByPeriod('1m');
        // First day of current month
        $firstDay = $this->date->gmtDate('Y-m-01');
        // Last day
        $lastDay = $this->date->gmtDate('Y-m-t');
        $salesOrderData = $this->salesOrderResource->getCustomerOrderSummary(
            $customerId,
            $firstDay,
            $lastDay
        );

        foreach ($dates as $date) {
            $keyData = array_search($date, array_column($salesOrderData, 'created_at'));
            $data[] = [
                "x" => $date,
                "y" => $keyData ? $salesOrderData[$keyData]['quantity'] : 0
            ];
        }

        return $data;
    }
}
