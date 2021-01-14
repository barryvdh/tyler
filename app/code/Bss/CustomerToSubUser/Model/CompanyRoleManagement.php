<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CustomerToSubUser\Api\CompanyRoleManagementInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterface;

/**
 * Class CompanyRoleManagement - Manage company roles
 */
class CompanyRoleManagement implements CompanyRoleManagementInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Bss\CompanyAccount\Api\SubRoleRepositoryInterface
     */
    private $roleRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
    ) {
        $this->logger = $logger;
        $this->searchBuilder = $searchBuilder;
        $this->customerRepository = $customerRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @inheritDoc
     */
    public function getListByCompanyAccount($emailOrId, int $websiteId): array
    {
        try {
            if (filter_var($emailOrId, FILTER_VALIDATE_EMAIL)) {
                $emailOrId = $this->customerRepository->get($emailOrId, $websiteId)->getId();
            }

            $this->searchBuilder->addFilter(
                SubRoleInterface::CUSTOMER_ID,
                $emailOrId
            );

            $searchResult = $this->roleRepository->getList($this->searchBuilder->create());

            return $searchResult->getItems();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
