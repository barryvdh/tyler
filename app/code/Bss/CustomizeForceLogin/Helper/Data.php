<?php
declare(strict_types=1);

namespace Bss\CustomizeForceLogin\Helper;

use Bss\ForceLogin\Helper\Data as ForceLoginHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\Url\EncoderInterface;

/**
 * Helper Data
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

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
     * @param EncoderInterface $urlEncoder
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ForceLoginHelper $forceLoginHelper,
        Session $customerSession,
        EncoderInterface $urlEncoder
    ) {
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->forceLoginHelper = $forceLoginHelper;
        $this->urlEncoder = $urlEncoder;
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

    /**
     * Get referer param for login action
     *
     * @param string $refererUrl
     * @param bool $encode
     * @return array
     */
    public function getLoginRefererParam($refererUrl, $encode = true)
    {
        $param = [];
        $redirectConfig = $this->forceLoginHelper->getRedirectUrl();

        if ($refererUrl &&
            $redirectConfig == "previous" ||
            ($redirectConfig == "customer/account/index" && !$this->forceLoginHelper->isRedirectDashBoard())
        ) {
            $url = $encode ? $this->urlEncoder->encode($refererUrl) : $refererUrl;
            $param = [
                \Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME => $url
            ];
        }

        return $param;
    }

    /**
     * Get redirect url
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        $redirectConfig = $this->forceLoginHelper->getRedirectUrl();

        switch ($redirectConfig) {
            case "customurl":
                return $this->forceLoginHelper->getCustomUrl();
            case "customer/account/index":
                return "customer/account/index";
            case "home":
            default:
                return "";
        }
    }
}
