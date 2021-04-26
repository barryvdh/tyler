<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Checkout\Helper;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class Data
 * Fix bug when sub-user is admin
 */
class Data extends \Bss\CompanyAccount\Plugin\Checkout\Helper\Data
{
    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Data constructor.
     *
     * @param \Bss\CompanyAccount\Helper\Data $helper
     * @param PermissionsChecker $permissionsChecker
     * @param \Magento\Checkout\Model\Cart $cart
     */
    public function __construct(
        \Bss\CompanyAccount\Helper\Data $helper,
        PermissionsChecker $permissionsChecker,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->permissionsChecker = $permissionsChecker;
        $this->cart = $cart;
        parent::__construct($helper, $permissionsChecker, $cart);
    }

    /**
     * Disable onepage checkout if sub-user max order amount is invalid
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param bool $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanOnepageCheckout(
        \Magento\Checkout\Helper\Data $subject,
        $result
    ) {
        if ($this->helper->isEnable() && $this->customerSession->getSubUser()) {
            $orderAmount = $this->cart->getQuote()->getBaseSubtotal();
            $cantAccessWithOrderAmount = $this->permissionsChecker
                ->isDenied(Permissions::MAX_ORDER_AMOUNT, $orderAmount);
            $cantAccessWithOrderPerDay = $this->permissionsChecker
                ->isDenied(Permissions::MAX_ORDER_PERDAY);
            if ($cantAccessWithOrderAmount === false || $cantAccessWithOrderPerDay === false) {
                return $result;
            }
            if ($cantAccessWithOrderAmount['is_denied'] || $cantAccessWithOrderPerDay['is_denied']) {
                return false;
            }
        }
        return $result;
    }
}
