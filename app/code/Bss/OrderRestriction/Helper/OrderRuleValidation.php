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

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * OrderRuleValidation constructor.
     *
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        OrderRuleRepositoryInterface $orderRuleRepository,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        ConfigProvider $configProvider
    ) {
        $this->orderRuleRepository = $orderRuleRepository;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->date = $date;
        $this->configProvider = $configProvider;
    }

    /**
     * Validate current customer can place order
     *
     * @param int|null $customerId
     * @param int $newQty - for validate from add qty to cart or update cart
     * @param int|null $itemId - update specific item - need to calculation
     * @param bool $isUpdateAll - update all qty of items in cart
     *
     * @return array - List error msg, empty is can place order
     */
    public function canPlaceOrder($customerId = null, $newQty = 0, $itemId = null, $isUpdateAll = false)
    {
        if (!$this->configProvider->isEnabled()) {
            return [];
        }

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
            $remainQty = $quote->getItemsSummaryQty() + $newQty;

            if ($itemId) {
                $remainQty = 0;

                // Get the sump qty of other item except updated item id
                foreach ($quote->getItems() as $item) {
                    if ($item->getItemId() != $itemId) {
                        $remainQty += $item->getQty();
                    }
                }

                // Plus to new updated qty
                $remainQty += $newQty;
            }

            if ($isUpdateAll) {
                $remainQty = $newQty;
            }
            if ($orderRule->getQtyPerOrder() < $remainQty) {
                $restrictMsg[] = __(
                    "You have reached maximum quantity allowed (%1) for this order" .
                    ". Please checkout before purchasing more",
                    $orderRule->getQtyPerOrder()
                );
            }

            if ($orderRule->getOrdersPerMonth() <= $this->getOrderCount($customerId)) {
                $restrictMsg[] = __(
                    "You have reached your order limit (%1) this month.",
                    $orderRule->getOrdersPerMonth()
                );
            }

            return $restrictMsg;
        } catch (\Exception $e) {
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
