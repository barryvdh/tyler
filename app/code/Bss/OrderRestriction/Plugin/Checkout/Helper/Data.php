<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Checkout\Helper;

use Bss\OrderRestriction\Helper\OrderRuleValidation;
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
class Data
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $customerSesison;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var OrderRuleValidation
     */
    private $orderRuleValidation;

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
     * @param OrderRuleValidation $orderRuleValidation
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        ManagerInterface $messageManager,
        OrderRuleValidation $orderRuleValidation,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->logger = $logger;
        $this->customerSesison = $customerSession;
        $this->messageManager = $messageManager;
        $this->orderRuleValidation = $orderRuleValidation;
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
            if ($this->customerSesison->isLoggedIn()) {
                $customerId = $this->customerSesison->getCustomerId();
                $validationResult = $this->orderRuleValidation->canPlaceOrder($customerId);

                if ($validationResult && $this->isAllowedShowNoticeController()) {
                    $this->messageManager->getMessages(true);
                    $this->messageManager->addErrorMessage(implode(", ", $validationResult));
                }

                return empty($validationResult);
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
