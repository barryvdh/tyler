<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Api;

/**
 * Interface CompanyRoleManagement
 */
interface CompanyRoleManagementInterface
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
    \Bss\CustomerToSubUser\Model\CompanyAccountResponse;
}
