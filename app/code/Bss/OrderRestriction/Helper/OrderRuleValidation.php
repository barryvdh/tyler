<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class OrderRuleValidation
 *
 * Checking if the current customer responds with the applicable ordering rule
 */
class OrderRuleValidation
{
    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    // @codingStandardsIgnoreLine
    public function __construct(
        OrderRuleRepositoryInterface $orderRuleRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->orderRuleRepository = $orderRuleRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->date = $date;
    }

    /**
     * @param int|null $customerId
     */
    public function execute($customerId = null)
    {
        if (!$customerId) {
            $customerId = $this->customerSession->getCustomerId();
        }

        try {
            $canPlaceOrder = false;
            $orderRule = $this->orderRuleRepository->getByCustomerId($customerId);

            if (!$orderRule) {
                return $canPlaceOrder;
            }

            $quote = $this->checkoutSession->getQuote();
            $canPlaceOrder = $orderRule->getQtyPerOrder() >= $quote->getItemsSummaryQty();

            $currentMonth = $this->date->gmtDate();

            $orders = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('customer_id', $customerId)->addFilter(
                        'created_at',
                        $currentMonth
                    )->create()
            );
            dd($orders->getItems());
        } catch (\Exception $e) {
            dd($e);
        }
    }
}
