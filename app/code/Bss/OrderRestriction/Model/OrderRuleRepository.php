<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Model\OrderRuleFactory;
use Bss\OrderRestriction\Model\ResourceModel\OrderRule as OrderRuleResource;
use Bss\OrderRestriction\Exception\CouldNotLoadException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Order rule repository model
 */
class OrderRuleRepository implements OrderRuleRepositoryInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Bss\OrderRestriction\Model\OrderRuleFactory
     */
    private $orderRuleFactory;

    /**
     * @var OrderRuleResource
     */
    private $orderRuleResource;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRuleFactory $orderRuleFactory,
        OrderRuleResource $orderRuleResource
    ) {
        $this->logger = $logger;
        $this->orderRuleFactory = $orderRuleFactory;
        $this->orderRuleResource = $orderRuleResource;
    }
    /**
     * @inheritDoc
     */
    public function get($customerId)
    {
        try {
            $orderRule = $this->orderRuleFactory->create();
            $this->orderRuleResource->load($orderRule, $customerId);

            return $orderRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotLoadException(__("Could not get the customer order rule data. Please review the log!"));
        }
    }

    /**
     * @param \Bss\OrderRestriction\Api\Data\OrderRuleInterface $orderRule
     * @return bool
     * @throws CouldNotSaveException
     */
    public function save($orderRule)
    {
        try {
            $this->orderRuleResource->save($orderRule);

            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(__("Something went wrong! Please review the log!"));
        }
    }
}
