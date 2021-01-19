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
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
     * Validate current customer can place order
     *
     * @param int|null $customerId
     *
     * @return array - List error msg, empty is can place order
     */
    public function canPlaceOrder($customerId = null)
    {
        if (!$customerId) {
            $customerId = $this->customerSession->getCustomerId();
        }

        try {
            $restrictMsg = [];
            $orderRule = $this->orderRuleRepository->getByCustomerId($customerId);

            // No assigned rule -> can
            if (!$orderRule->getId()) {
                return $restrictMsg;
            }

            // No assign value -> can
            if ($orderRule->getQtyPerOrder() == null && $orderRule->getOrdersPerMonth() == null) {
                return $restrictMsg;
            }

            $quote = $this->checkoutSession->getQuote();

            if ($orderRule->getQtyPerOrder() < $quote->getItemsSummaryQty()) {
                $restrictMsg[] = __("Your order can only order %1 item at a time.", $orderRule->getQtyPerOrder());
            }

            if ($orderRule->getOrdersPerMonth() <= $this->getOrderCount($customerId)) {
                $restrictMsg[] = __(
                    "You have reached your order limit (%1) this month.",
                    $orderRule->getOrdersPerMonth()
                );
            }

            return $restrictMsg;
        } catch (\Execption $e) {
            return [];
        }
    }

    /**
     * Get total order of customer id in current month
     *
     * @param int $customerId
     * @return int
     */
    public function getOrderCount($customerId)
    {
        try {
            // First day of current month
            $firstDay = $this->date->gmtDate('Y-m-01');
            // Last day
            $lastDay = $this->date->gmtDate('Y-m-t');
            $orders = $this->orderRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('customer_id', $customerId)->addFilter(
                        'created_at',
                        $firstDay,
                        'from'
                    )->addFilter(
                        'created_at',
                        $lastDay,
                        'to'
                    )->create()
            );

            return $orders->getTotalCount();
        } catch (\Exception $e) {
            return 0;
        }
    }
}
