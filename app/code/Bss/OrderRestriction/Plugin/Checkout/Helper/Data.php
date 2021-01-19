<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Checkout\Helper;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Helper\OrderRuleValidation;
use Magento\Checkout\Helper\Data as BePlugged;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Plugin to modify the checkout helper data class
 *
 * @see BePlugged
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
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

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
        OrderRuleRepositoryInterface $orderRuleRepository,
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        OrderRuleValidation $orderRuleValidation
    ) {
        $this->logger = $logger;
        $this->customerSesison = $customerSession;
        $this->orderRuleRepository = $orderRuleRepository;
        $this->checkoutSession = $checkoutSession;
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
                $this->orderRuleValidation->execute($customerId);
                $orderRule = $this->orderRuleRepository->getByCustomerId($customerId);

                if (!$orderRule->getId()) {
                    return $result;
                }

//                if ($qty = $orderRule->getQtyPerOrder()) {
//                    $this->messageManager->addErrorMessage("vadu hihi");
//                    return $qty >= $this->checkoutSession->getQuote()->getItemsSummaryQty();
//                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $result;
    }
}
