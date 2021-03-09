<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Plugin\Controller\Account;

use Bss\ForceLogin\Helper\Data;

/**
 * Class LoginPost
 * Redirect to previous page fix
 */
class LoginPost
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $forceLoginHelper;

    /**
     * @var \Bss\CustomizeForceLogin\Model\AccountRedirect
     */
    private $accountRedirect;

    /**
     * LoginPost constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Data $forceLoginHelper
     * @param \Bss\CustomizeForceLogin\Model\AccountRedirect $accountRedirect
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Data $forceLoginHelper,
        \Bss\CustomizeForceLogin\Model\AccountRedirect $accountRedirect
    ) {
        $this->logger = $logger;
        $this->forceLoginHelper = $forceLoginHelper;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * Redirect to previous page fix
     *
     * @param \Bss\ForceLogin\Plugin\Customer\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterExecute(
        \Bss\ForceLogin\Plugin\Customer\LoginPost $subject,
        $resultRedirect
    ) {
        if (!$this->accountRedirect->getCustomerSession()->isLoggedIn()) {
            return $resultRedirect;
        }
        try {
            $isEnabled = $this->forceLoginHelper->isEnable();
            $previousUrl = $this->accountRedirect->getRefererUrl();
            $moduleAfterLoginConfig = $this->forceLoginHelper->getRedirectUrl();
            
            if ($isEnabled && $previousUrl && $moduleAfterLoginConfig == "previous") {
                $this->accountRedirect->clearRefererUrl();
                $resultRedirect->setUrl($previousUrl);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $resultRedirect;
    }
}
