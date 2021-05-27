<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Plugin\Controller\Account;

use Bss\ForceLogin\Controller\Account\Login as BePlugged;
use Bss\CustomizeForceLogin\Model\AccountRedirect;

/**
 * Class Login
 * Set previous url to session.
 * @see BePlugged
 */
class Login
{
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * Login constructor.
     *
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        AccountRedirect $accountRedirect
    ) {
        $this->redirect = $redirect;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * Set previous url to session. To avoid error when login failed and redirect to previous page
     *
     * @param BePlugged $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(BePlugged $subject)
    {
        $refererUrl = $this->redirect->getRefererUrl();
        // If the referer url is login page, no update referer url
        $keepPrevious = preg_match('/customer\/account\/login/', $refererUrl);

        if ($keepPrevious === 0) {
            $this->accountRedirect->setRefererUrl($refererUrl);
        }
    }
}
