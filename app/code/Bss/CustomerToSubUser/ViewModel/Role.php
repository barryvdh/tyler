<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\ViewModel;

use Bss\CompanyAccount\Model\SubRoleRepository;

/**
 * Role view model
 */
class Role implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SubRoleRepository
     */
    private $roleRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder,
        SubRoleRepository $roleRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $options = [];

        $roles = $this->getRoles();

        foreach ($roles as $role) {
            $option = [
                "label" => $role->getRoleName(),
                "value" => $role->getRoleId(),
                "company_account_id" => $role->getCompanyAccount() ? $role->getCompanyAccount() : 'admin'
            ];

            $options[] = $option;
        }
        array_walk(
            $options,
            function (&$item) {
                $item['__disableTmpl'] = true;
            }
        );

        return $options;
    }

    /**
     * Get list roles
     *
     * @return \Bss\CompanyAccount\Api\Data\SubRoleInterface[]
     */
    private function getRoles()
    {
        return $this->roleRepository->getList(
            $this->searchBuilder->create()
        )->getItems();
    }
}
