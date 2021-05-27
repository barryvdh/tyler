<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Observer;

use Magento\Framework\Event\Observer;

/**
 * The sub-user still was assigned to current customer
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TheSubUserStillAssignToCurrent implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $companyAccountHelper;

    /**
     * @var \Bss\CompanyAccount\Api\SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Bss\CustomerToSubUser\Model\CompanyAccountManagement
     */
    private $companyAccountManagement;

    /**
     * TheSubUserStillAssignToCurrent constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Bss\CompanyAccount\Helper\Data $companyAccountHelper
     * @param \Bss\CompanyAccount\Api\SubUserRepositoryInterface $subUserRepository
     * @param \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Bss\CompanyAccount\Helper\Data $companyAccountHelper,
        \Bss\CompanyAccount\Api\SubUserRepositoryInterface $subUserRepository,
        \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement
    ) {
        $this->customerSession = $customerSession;
        $this->companyAccountHelper = $companyAccountHelper;
        $this->subUserRepository = $subUserRepository;
        $this->companyAccountManagement = $companyAccountManagement;
    }
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (!$this->customerSession->getSubUser() &&
            !$this->customerSession->isLoggedIn()
        ) {
            return $this;
        }

        if (!$this->customerSession->getSubUser() &&
            $this->customerSession->isLoggedIn()
        ) {
            $subUser = $this->companyAccountManagement->getCompanyAccountBySubEmail(
                $this->customerSession->getCustomer()->getEmail(),
                $this->customerSession->getCustomer()->getWebsiteId()
            )->getSubUser();

            if ($subUser->getSubUserId()) {
                $this->logout(__("Your account was assigned as Sub-user."));
            }

            return $this;
        }

        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();

        $subUser = $this->subUserRepository->getById($subUser->getSubUserId());

        if ($subUser->getCompanyCustomerId() != $this->customerSession->getCustomerId()) {
            $this->logout(__("Your account was assigned to other Company Account."));
        }

        return $this;
    }

    /**
     * Logout customer
     *
     * @param string $message
     */
    private function logout($message)
    {
        $customerId = $this->customerSession->getCustomerId();
        $this->customerSession->logout()
            ->setBeforeAuthUrl($this->companyAccountHelper->getRedirect()->getRefererUrl())
            ->setLastCustomerId($customerId);
        $this->customerSession->clearStorage();
        $this->companyAccountHelper->getMessageManager()->addErrorMessage(
            __('The current session has expired.') .
            $message .
            __('Please reload to update page.')
        );
    }
}
