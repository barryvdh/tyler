<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CustomerToSubUser\Api\CompanyAccountManagementInterface;
use Bss\CompanyAccount\Api\Data\SubRoleInterface;
use Bss\CompanyAccount\Api\SubUserManagementInterface;

/**
 * Class CompanyRoleManagement - Manage company roles
 *
 * `@SuppressWarnings(PHPMD.CouplingBetweenObjects)`
 */
class CompanyAccountManagement implements CompanyAccountManagementInterface
{
    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    private $subUserInterfaceFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerInterfaceFactory;

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

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param SubUserManagementInterface $subUserManagement
     * @param CompanyAccountResponseFactory $companyAccountResponseFactory
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserInterfaceFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
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
