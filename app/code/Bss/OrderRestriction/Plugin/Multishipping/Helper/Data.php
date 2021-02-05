<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Multishipping\Helper;

use Bss\OrderRestriction\Helper\AvailableToAddCart;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Multishipping\Helper\Data as BePlugged;

/**
 * Class Data - Validate the current customer can place multi shipping order
 *
 * @see BePlugged
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var AvailableToAddCart
     */
    private $orderRuleValidation;

    /**
     * Data constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param AvailableToAddCart $orderRuleValidation
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        AvailableToAddCart $orderRuleValidation
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->orderRuleValidation = $orderRuleValidation;
    }

    /**
     * Disable multishipping checkout if sub-user max order amount not allowed
     *
     * @param \Magento\Multishipping\Helper\Data $subject
     * @param bool $result
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsMultishippingCheckoutAvailable(
        BePlugged $subject,
        $result
    ) {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $productData = [];
                $quote = $subject->getQuote();
                foreach ($quote->getAllItems() as $item) {
                    $productDataItem = [
                        "product_id" => $item->getProductId(),
                        "qty" => $item->getQty()
                    ];

                    $productData[] = $productDataItem;
                }

                return $this->orderRuleValidation->canAddProductToCart($productData);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }
}
