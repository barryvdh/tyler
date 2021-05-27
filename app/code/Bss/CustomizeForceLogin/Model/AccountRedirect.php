<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Model;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class AccountRedirect
 * Set and retrieve previous url to cookie
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountRedirect
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * AccountRedirect constructor.
     *
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface,
        CustomerSession $customerSession
    ) {
        $this->urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
    }

    /**
     * Get previous url
     *
     * @return string|null
     */
    public function getRefererUrl()
    {
        return $this->customerSession->getLoginRefererUrl();
    }

    /**
     * Set previous url
     *
     * @param string $url
     */
    public function setRefererUrl($url)
    {
        $this->customerSession->setLoginRefererUrl($url);
    }

    /**
     * Clear previous url
     */
    public function clearRefererUrl()
    {
        $this->customerSession->unsLoginRefererUrl();
    }

    /**
     * Get customer session object
     *
     * @return CustomerSession
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
}
