<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Helper;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $adminSession;

    /**
     * Data constructor.
     *
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $adminSession
    ) {
        $this->adminSession = $adminSession;
    }

    /**
     * Current user is brand manager
     *
     * @return bool
     */
    public function isBrandManager()
    {
        $role = $this->adminSession->getUser()->getRole()->getRoleName();

        return $role === "Brand Manager";
    }
}
