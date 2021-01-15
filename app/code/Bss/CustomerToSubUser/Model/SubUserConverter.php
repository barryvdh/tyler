<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface as SubUser;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Class SubUserConverter convert normal customer to sub-user
 */
class SubUserConverter
{
    const SUB_USER_ENABLE = 1;
    const SUB_USER_MAIL_SENT = 1;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    private \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory;

    /**
     * @var SubUserRepositoryInterface
     */
    private SubUserRepositoryInterface $subUserRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomerToSubUser\Model\ResourceModel\Customer $customerResource,
        \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->logger = $logger;
        $this->customerResource = $customerResource;
        $this->subUserFactory = $subUserFactory;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Convert customer to sub user
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $companyAccountId
     * @param int $companyAccountRole
     */
    public function convertToSubUser(
        $customer,
        $companyAccountId,
        $companyAccountRole
    ) {
        try {
            $subUserData = [
                SubUser::NAME => $this->getCustomerFullName($customer),
                SubUser::ROLE_ID => $companyAccountRole,
                SubUser::STATUS => self::SUB_USER_ENABLE,
                SubUser::EMAIL => $customer->getEmail(),
                SubUser::PASSWORD => $this->customerResource->getEncryptCustomerPassword((int) $customer->getId()),
                SubUser::CUSTOMER_ID => $companyAccountId,
                SubUser::IS_SENT_MAIL => self::SUB_USER_MAIL_SENT
            ];

            $subUser = $this->subUserFactory->create();
            $subUser->setData($subUserData);

            $this->subUserRepository->save($subUser);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Get customer fullname
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return string
     */
    private function getCustomerFullName($customer): string
    {
        $fullName = $customer->getFirstname();

        if ($customer->getMiddlename()) {
            $fullName .= ' ' . $customer->getMiddlename() . ' ';
        }

        return $fullName . $customer->getLastname();
    }
}
