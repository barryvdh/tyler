<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\Data\OrderRuleInterface;
use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
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
     * @var OrderRuleFactory
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
    public function get($id)
    {
        return $this->getByField($id);
    }

    /**
     * @inheritDoc
     */
    public function getByCustomerId($customerId)
    {
        return $this->getByField($customerId, OrderRuleInterface::CUSTOMER_ID);
    }

    /**
     * Load object
     *
     * @param string $value
     * @param string $field
     * @return OrderRule
     */
    private function getByField($value, $field = null)
    {
        $orderRule = $this->orderRuleFactory->create();
        try {
            $this->orderRuleResource->load($orderRule, $value, $field);

            return $orderRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $orderRule;
            // throw new CouldNotLoadException
            // (__("Could not get the customer order rule data. Please review the log!"));
        }
    }
    /**
     * Save the order rule
     *
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
