<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Plugin\Model\Customer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as BePlugged;

/**
 * Plugin add company info was assigned to current customer
 */
class CompanyAccountAssignedInfoPlugin
{
    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    // @codingStandardsIgnoreLine
    public function __construct(
        SubUserManagementInterface $subUserManagement
    ) {
        $this->subUserManagement = $subUserManagement;
    }

    /**
     * Add company account information
     *
     * @param BePlugged $subject
     * @param array $loadedData
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(
        BePlugged $subject,
        $loadedData
    ) {
        foreach ($loadedData as &$customerData) {
            $subUser = $this->subUserManagement->getSubUserBy(
                $customerData['customer']['email'],
                SubUserInterface::EMAIL,
                $customerData['customer']['website_id']
            );
            if ($subUser) {
                $customerData['assign_to_company_account']['sub_id'] = $subUser->getSubUserId();
                $customerData['assign_to_company_account']['company_account_id'] = $subUser->getCompanyCustomerId();
                $customerData['assign_to_company_account']['role_id'] = $subUser->getRelatedRoleId();
            }
        }

        return $loadedData;
    }
}
