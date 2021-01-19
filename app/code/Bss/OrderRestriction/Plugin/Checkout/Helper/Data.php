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

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        ManagerInterface $messageManager,
        OrderRuleValidation $orderRuleValidation
    ) {
        $this->logger = $logger;
        $this->customerSesison = $customerSession;
        $this->messageManager = $messageManager;
        $this->orderRuleValidation = $orderRuleValidation;
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

                if ($validationResult) {
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
}
