<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Plugin\Model\Customer;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CustomerToSubUser\Model\CompanyAccountManagement;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement as BePlugged;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AccountManagement - reset password plugin
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AccountManagement
{
    /**
     * @var CompanyAccountManagement
     */
    private $companyAccManagement;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerSession
     */
    private $session;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\CustomerToSubUser\Model\ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var GetCustomerByToken
     */
    private $getByToken;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        CompanyAccountManagement $companyAccManagement,
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $session,
        StoreManagerInterface $storeManager,
        \Bss\CustomerToSubUser\Model\ResourceModel\Customer $customerResource,
        SubUserRepositoryInterface $subUserRepository,
        GetCustomerByToken $getByToken,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->companyAccManagement = $companyAccManagement;
        $this->logger = $logger;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->customerResource = $customerResource;
        $this->subUserRepository = $subUserRepository;
        $this->getByToken = $getByToken;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Set the customer email to session
     *
     * @param BePlugged $subject
     * @param string $email
     * @param string $resetToken
     * @param string $newPassword
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeResetPassword(
        BePlugged $subject,
        $email,
        $resetToken,
        $newPassword
    ) {
        if (!$email) {
            $customer = $this->getByToken->execute($resetToken);
        } else {
            $customer = $this->customerRepository->get($email);
        }

        $this->session->setNeedResetPwdCustomer($customer);

        return [$email, $resetToken, $newPassword];
    }

    /**
     * Set the sub-user password by customer password
     *
     * @param BePlugged $subject
     * @param bool $result
     * @return mixed
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResetPassword(
        BePlugged $subject,
        $result
    ) {
        try {

            /** @var \Magento\Customer\Model\Customer $customer */
            if ($customer = $this->session->getNeedResetPwdCustomer()) {
                $this->session->unsNeedResetPwdCustomer();

                $customerId = $customer->getId();
                $subUser = $this->companyAccManagement->getCompanyAccountBySubEmail(
                    $customer->getEmail(),
                    $customer->getWebsiteId()
                )->getSubUser();

                if ($customerId && $subUser->getSubUserId()) {
                    $customerHashedPassword = $this->customerResource->getEncryptCustomerPassword(
                        (int) $customerId
                    );

                    $subUser->setSubUserPassword($customerHashedPassword);

                    $this->subUserRepository->save($subUser);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }
}
