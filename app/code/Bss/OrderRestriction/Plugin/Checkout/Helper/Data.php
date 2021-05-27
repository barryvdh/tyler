<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Checkout\Helper;

use Bss\OrderRestriction\Helper\AvailableToAddCart;
use Magento\Checkout\Helper\Data as BePlugged;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Plugin to modify the checkout helper data class
 *
 * @see BePlugged
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Bss\OrderRestriction\Plugin\AbstractGetProductDataInQuote
{
    /**
     * @var string[]
     */
    private $allowedDisplayedNoticeControllers = [
        "multishipping_checkout_index",
        "checkout_index_index",
        "checkout_cart_index"
    ];

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * Data constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param ManagerInterface $messageManager
     * @param AvailableToAddCart $orderRuleValidation
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        ManagerInterface $messageManager,
        AvailableToAddCart $orderRuleValidation,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct(
            $logger,
            $customerSession,
            $orderRuleValidation
        );
        $this->messageManager = $messageManager;
        $this->request = $request;
    }

    /**
     * Check current customer has be set a order rule
     *
     * @param BePlugged $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanOnepageCheckout(
        BePlugged $subject,
        $result
    ) {
        try {
            if ($this->customerSession->isLoggedIn()) {
                $productData = $this->getProductData($subject->getQuote());
                $canAddProduct = $this->orderRuleValidation->canAddProductToCart($productData);

                if (!$canAddProduct && $this->isAllowedShowNoticeController()) {
                    $this->orderRuleValidation->getRestrictionMessages();
                }

                return $canAddProduct;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $result;
    }

    /**
     * Get allowed page to show notice whenever check the "can checkout order" status
     *
     * (Not include add to cart action)
     *
     * @return bool
     */
    private function isAllowedShowNoticeController(): bool
    {
        $fullAction = $this->request->getModuleName() . "_" .
            $this->request->getControllerName() . "_" .
            $this->request->getActionName();

        return in_array($fullAction, $this->allowedDisplayedNoticeControllers);
    }
}
