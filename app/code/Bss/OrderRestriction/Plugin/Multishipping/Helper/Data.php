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
class Data extends \Bss\OrderRestriction\Plugin\AbstractGetProductDataInQuote
{
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
                $productData = $this->getProductData($subject->getQuote());

                return $this->orderRuleValidation->canAddProductToCart($productData);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }
}
