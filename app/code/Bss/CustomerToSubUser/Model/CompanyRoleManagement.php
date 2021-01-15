<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CustomerToSubUser\Api\CompanyRoleManagementInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\SubUserManagementInterface;

/**
 * Class CompanyRoleManagement - Manage company roles
 */
class CompanyRoleManagement implements CompanyRoleManagementInterface
{
    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    protected \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserInterfaceFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory;

    /**
     * @var CompanyAccountResponseFactory
     */
    private CompanyAccountResponseFactory $companyAccountResponseFactory;

    /**
     * @var SubUserManagementInterface
     */
    private SubUserManagementInterface $subUserManagement;

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

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        SubUserManagementInterface $subUserManagement,
        \Bss\CustomerToSubUser\Model\CompanyAccountResponseFactory $companyAccountResponseFactory,
        \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserInterfaceFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory
    ) {
        $this->logger = $logger;
        $this->searchBuilder = $searchBuilder;
        $this->customerRepository = $customerRepository;
        $this->roleRepository = $roleRepository;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->subUserManagement = $subUserManagement;
        $this->companyAccountResponseFactory = $companyAccountResponseFactory;
        $this->subUserInterfaceFactory = $subUserInterfaceFactory;
        $this->customerInterfaceFactory = $customerInterfaceFactory;
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

            $filterByCompanyAccountId = $this->filterBuilder
                ->setField(SubRoleInterface::CUSTOMER_ID)
                ->setValue($emailOrId)
                ->setConditionType('eq')
                ->create();

            $adminRoleFilter = $this->filterBuilder
                ->setField(SubRoleInterface::CUSTOMER_ID)
                ->setConditionType('null')
                ->create();
            $filterGroups = $this->filterGroupBuilder
                ->addFilter($filterByCompanyAccountId)
                ->addFilter($adminRoleFilter)
                ->create();

            $this->searchBuilder->setFilterGroups([$filterGroups]);
            $searchResult = $this->roleRepository->getList($this->searchBuilder->create());

            return $searchResult->getItems();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @inheritDoc
     */
    public function getCompanyAccountBySubEmail(string $email, $websiteId):
    \Bss\CustomerToSubUser\Model\CompanyAccountResponse
    {
        /** @var \Bss\CustomerToSubUser\Model\CompanyAccountResponse $result */
        $result = $this->companyAccountResponseFactory->create();
        $subUser = $this->subUserInterfaceFactory->create();
        $customer = $this->customerInterfaceFactory->create();
        $result->setSubUser($subUser);
        $result->setCompanyCustomer($customer);

        try {
            $subUser = $this->subUserManagement->getSubUserBy($email, SubUserInterface::EMAIL, $websiteId);

            if (!$subUser) {
                return $result;
            }
            $customer = $this->subUserManagement->getCustomerBySubUser($subUser, $websiteId);

            $result->setSubUser($subUser);
            $result->setCompanyCustomer($customer);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $result;
    }
}
