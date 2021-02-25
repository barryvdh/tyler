<?php
declare(strict_types=1);

namespace Bss\CustomizeForceLogin\Helper;

use Bss\ForceLogin\Helper\Data as ForceLoginHelper;
use Magento\Customer\Model\Session;

/**
 * Helper Data
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
     * @var ForceLoginHelper
     */
    private $forceLoginHelper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Data constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ForceLoginHelper $forceLoginHelper
     * @param Session $customerSession
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ForceLoginHelper $forceLoginHelper,
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->forceLoginHelper = $forceLoginHelper;
    }

    /**
     * Is force login product page and guest customer can not add to cart
     *
     * @return bool
     */
    public function cantAddToCart(): bool
    {
        $forceLoginEnable = $this->forceLoginHelper->isEnable();
        $notLogin = $this->customerSession->isLoggedIn();
        return !$notLogin &&
            $forceLoginEnable &&
            $this->forceLoginHelper->isEnableProductPage();
    }

    /**
     * Get force login message when redirect to login page
     *
     * @return string
     */
    public function getForceLoginMessage()
    {
        return $this->forceLoginHelper->getAlertMessage();
    }
}
