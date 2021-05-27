<?php
declare(strict_types=1);
namespace Bss\CustomerToSubUser\Plugin\Model;

use Bss\CompanyAccount\Api\Data\SubRoleInterface;

/**
 * Class SubUser
 * Fix admin role checker
 */
class SubUser
{
    /**
     * Fix admin role checker
     *
     * @param \Bss\CompanyAccount\Model\SubUser $subject
     * @param bool $canAccess
     * @param int $value
     * @param SubRoleInterface $role
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanAccess(
        \Bss\CompanyAccount\Model\SubUser $subject,
        $canAccess,
        $value,
        $role
    ) {
        return $role->getRoleType() != "0" && empty($role->getRoleType()) ? false :
            in_array(
                $value,
                explode(',', $role->getRoleType())
            );
    }
}
