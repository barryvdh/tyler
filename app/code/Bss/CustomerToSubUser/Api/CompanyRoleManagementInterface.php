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
}
