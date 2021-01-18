<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model\Customer;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Exception\CouldNotLoadException;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as BePlugged;

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

    // @codingStandardsIgnoreLine
    public function __construct(
        OrderRuleRepositoryInterface $orderRuleRepository,
        \Psr\Log\LoggerInterface $logger
    ) {

        $this->orderRuleRepository = $orderRuleRepository;
        $this->logger = $logger;
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
                $orderRule = $this->orderRuleRepository->get($customerData['customer']['entity_id']);
            } catch (CouldNotLoadException $e) {
                continue;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                continue;
            }

            if ($orderRule->getCustomerId()) {
                $customerData['order_restriction'] = $orderRule->getData();
            }
        }

        return $loadedData;
    }
}
