<?php
declare(strict_types=1);
namespace Bss\OrderRestriction\Block\Customer\Account;

use Bss\OrderRestriction\Api\Data\OrderRuleInterface;
use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Helper\OrderRuleValidation;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Helper\Session\CurrentCustomer;

/**
 * Class OrderRestrictionInfo
 */
class OrderRestrictionInfo extends \Magento\Framework\View\Element\Template
{
    /**
     * @var OrderRuleInterface|null
     */
    private $orderRule;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var OrderRuleValidation
     */
    private $ruleValidation;

    /**
     * OrderRestrictionInfo constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CurrentCustomer $currentCustomer
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CurrentCustomer $currentCustomer,
        OrderRuleRepositoryInterface $orderRuleRepository,
        OrderRuleValidation $ruleValidation,
        Context $context,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->currentCustomer = $currentCustomer;
        $this->orderRuleRepository = $orderRuleRepository;
        $this->ruleValidation = $ruleValidation;
        parent::__construct($context, $data);
    }

    /**
     * Get current customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        try {
            return $this->currentCustomer->getCustomer();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return null;
        }
    }

    /**
     * Get related order rule
     *
     * @return OrderRuleInterface|null
     */
    public function getOrderRule()
    {
        try {
            if (!$this->orderRule) {
                $customer = $this->getCustomer();
                if (!$customer) {
                    $this->orderRule = null;
                }
                $orderRule = $this->orderRuleRepository->getByCustomerId($customer->getId());
                if ($orderRule->getId()) {
                    $this->orderRule = $orderRule;
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->orderRule = null;
        }

        return $this->orderRule;
    }

    /**
     * Get number of order customer was placed
     *
     * @return int
     */
    public function getCustomerOrderCount()
    {
        try {
            if ($customer = $this->getCustomer()) {
                return $this->ruleValidation->getOrderCount($customer->getId());
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return 0;
    }
}
