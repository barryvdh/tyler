<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Api;

/**
 * Interface CompanyRoleManagement
 */
interface CompanyAccountManagementInterface
{
    /**
     * Get list roles by company account
     *
     * @param string|int $emailOrId
     * @param int $websiteId
     * @return \Bss\CompanyAccount\Api\Data\SubRoleInterface[]
     */
    public function getListByCompanyAccount($emailOrId, int $websiteId): array;

    /**
     * Get Company account
     *
     * @param string $email
     * @param int $websiteId
     * @return \Bss\CustomerToSubUser\Api\Data\CompanyAccountResponseInterface
     */
    public function getCompanyAccountBySubEmail(string $email, $websiteId):
    \Bss\CustomerToSubUser\Api\Data\CompanyAccountResponseInterface;

    /**
     * Get list custom attribute by customer id
     *
     * @param int $customerId
     * @return \Magento\Framework\Api\AttributeValue[]
     */
    public function getCustomerCustomAttributes($customerId);
}
