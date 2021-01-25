<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model\Customer;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Exception\CouldNotLoadException;
use Bss\OrderRestriction\Helper\ConfigProvider;
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

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * DataProviderWithDefaultAddresses constructor.
     *
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param Date $dateRetriever
     * @param SalesOrder $salesOrderResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param OrderRuleValidation $orderRuleValidation
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        OrderRuleRepositoryInterface $orderRuleRepository,
        \Psr\Log\LoggerInterface $logger,
        Date $dateRetriever,
        SalesOrder $salesOrderResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        OrderRuleValidation $orderRuleValidation,
        ConfigProvider $configProvider
    ) {

        $this->orderRuleRepository = $orderRuleRepository;
        $this->logger = $logger;
        $this->dateRetriever = $dateRetriever;
        $this->salesOrderResource = $salesOrderResource;
        $this->date = $date;
        $this->orderRuleValidation = $orderRuleValidation;
        $this->configProvider = $configProvider;
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
        if (!$this->configProvider->isEnabled()) {
            return $loadedData;
        }

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

        return $loadedData;
    }
}
