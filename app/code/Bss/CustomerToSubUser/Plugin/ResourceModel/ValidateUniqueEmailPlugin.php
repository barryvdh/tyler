<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Plugin\ResourceModel;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResource;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Class ValidateUniqueEmailPlugin
 */
class ValidateUniqueEmailPlugin
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected \Magento\Framework\App\ResourceConnection $resource;

    /**
     * @var CustomerResource
     */
    private CustomerResource $customerResource;

    /**
     * @var CustomerFactory
     */
    private CustomerFactory $customerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private \Psr\Log\LoggerInterface $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private \Magento\Framework\App\RequestInterface $request;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource,
        CustomerResource $customerResource,
        CustomerFactory $customerFactory
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->resource = $resource;
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Validate Unique email after save sub-user
     *
     * If is convert from normal customer to sub-user then exclude validate with self email
     *
     * @param \Bss\CompanyAccount\Model\ResourceModel\SubUser $subject
     * @param callable $process
     * @param int $customerId
     * @param string $email
     * @param int $subId
     * @return ValidateUniqueEmailPlugin
     * @throws AlreadyExistsException
     */
    public function aroundValidateUniqueSubEmail(
        \Bss\CompanyAccount\Model\ResourceModel\SubUser $subject,
        callable $process,
        $customerId,
        $email,
        $subId
    ) {
        $assignAsSubUser = $this->request->getParam('assign_to_company_account');
        $currentCustomerId = $this->request->getParam('customer')['entity_id'] ?? null;

        if ($assignAsSubUser && $currentCustomerId) {
            /** @var \Magento\Customer\Model\Customer $customer */
            $customer = $this->customerFactory->create();

            $this->customerResource->load($customer, $customerId);

            $subUserTableName = $this->resource->getTableName($subject::TABLE);
            $customerTableName = $this->resource->getTableName('customer_entity');

            $connection = $this->resource->getConnection();

            $subUserBind = ['sub_email' => $email];
            $customerBind = ['email' => $email];

            /* Fetch sub-user of specific website */
            $subUserSelect = $connection->select()->from(
                $subUserTableName,
                [$subject::ID]
            )->join(
                $customerTableName,
                $subUserTableName
                . '.customer_id = ' . $customerTableName . '.entity_id'
            )
                ->where('sub_email = :sub_email');

            $customerSelect = $connection->select()->from(
                $customerTableName,
                ['entity_id']
            )->where('email = :email');

            if ($customer->getSharingConfig()->isWebsiteScope()) {
                $websiteId = (int)$customer->getWebsiteId();

                $subUserBind['website_id'] = $websiteId;
                $subUserSelect->where('website_id = :website_id');

                $customerBind['website_id'] = $websiteId;
                $customerSelect->where('website_id = :website_id');
            }

            if ($subId) {
                $subUserBind[$subject::ID] = $subId;
                $subUserSelect->where($subject::ID . ' != :' . $subject::ID);
            }

            $customerSelect->where('entity_id != ?', $currentCustomerId);

            $subUserResult = $connection->fetchOne($subUserSelect, $subUserBind);
            $customerResult = $connection->fetchOne($customerSelect, $customerBind);

            if ($subUserResult || $customerResult) {
                throw new AlreadyExistsException(
                    __('A user with the same email address already exists in an associated website.')
                );
            }

            return $this;
        }

        return $process($customerId, $email, $subId);
    }

    /**
     * Validate Unique email after save customer
     *
     * @param \Bss\CompanyAccount\Model\ResourceModel\Customer $subject
     * @param callable $process
     * @param string $customerEmail
     * @param int $websiteId
     * @return mixed
     */
    public function aroundValidateUniqueCustomer(
        \Bss\CompanyAccount\Model\ResourceModel\Customer $subject,
        callable $process,
        $customerEmail,
        $websiteId
    ) {
        $assignAsSubUser = $this->request->getParam('assign_to_company_account');
        $currentCustomerId = $this->request->getParam('customer')['entity_id'] ?? null;

        dd($currentCustomerId, $assignAsSubUser);
        if ($assignAsSubUser && $currentCustomerId && $assignAsSubUser['company_account_id']) {
            /** Begin validate unique email compatible with our module */
            $connection = $this->resource->getConnection();

            $customer = $this->customerFactory->create()->setWebsiteId($websiteId);
            $this->customerResource->loadByEmail($customer, $customerEmail);

            $customerBind = [
                'sub_email' => $customerEmail,
                'customerId' => $currentCustomerId
            ];
            $subUserTableName = $this->resource->getTableName('bss_sub_user');
            $customerTableName = $this->resource->getTableName('customer_entity');

            /* Fetch sub-user of specific website */
            $subUserSelect = $connection->select()->from(
                ['sub_user' => $subUserTableName],
                ['sub_id']
            )->join(
                ['customer' => $customerTableName],
                'sub_user.customer_id = customer.entity_id'
            )
                ->where('sub_email = :sub_email')
                ->where('customer.entity_id != :customerId');

            if ($customer->getSharingConfig()->isWebsiteScope()) {
                $subUserSelect->where('website_id = :website_id');
                $customerBind['website_id'] = $websiteId;
            }

            vadu_log($subUserSelect->__toString());

            return $connection->fetchOne($subUserSelect, $customerBind);
        }

        return $process($customerEmail, $websiteId);
    }
}
