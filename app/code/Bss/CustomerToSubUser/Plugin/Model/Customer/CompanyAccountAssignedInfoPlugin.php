<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Plugin\Model\Customer;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses as BePlugged;

/**
 * Plugin add company info was assigned to current customer
 */
class CompanyAccountAssignedInfoPlugin
{
    const ASSIGN_FIELD_DATA = "assign_to_company_account";

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SubUserManagementInterface
     */
    private $subUserManagement;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $countryFactory;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    private $addressFactory;

    /**
     * CompanyAccountAssignedInfoPlugin constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param SubUserManagementInterface $subUserManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        SubUserManagementInterface $subUserManagement,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {
        $this->logger = $logger;
        $this->subUserManagement = $subUserManagement;
        $this->customerRepository = $customerRepository;
        $this->countryFactory = $countryFactory;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Add company account information for admin customer form
     *
     * @param BePlugged $subject
     * @param array $loadedData
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
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
                $customerData[self::ASSIGN_FIELD_DATA]['is_sub_user'] = (bool) $subUser->getSubUserId();
                $customerData[self::ASSIGN_FIELD_DATA]['sub_id'] = $subUser->getSubUserId();
                $customerData[self::ASSIGN_FIELD_DATA]['company_account_id'] = $subUser->getCompanyCustomerId();
                $customerData[self::ASSIGN_FIELD_DATA]['role_id'] = $subUser->getRelatedRoleId();
                $this->mappingCompanyAccountCustomAttributes($customerData[self::ASSIGN_FIELD_DATA]);

                $companyAccount = $this->customerRepository->getById($subUser->getCompanyCustomerId());
                $companyAccountAddress = $this->addressFactory->create()
                    ->load($companyAccount->getDefaultBilling());
                $customerData['default_billing_address'] = $this->prepareDefaultAddress(
                    $companyAccountAddress
                );
                $companyAccountAddress = $this->addressFactory->create()
                    ->load($companyAccount->getDefaultShipping());
                $customerData['default_shipping_address'] = $this->prepareDefaultAddress(
                    $companyAccountAddress
                );
            }
        }

        return $loadedData;
    }

    /**
     * Prepare default address data.
     *
     * @param Address|false $address
     * @return array
     */
    private function prepareDefaultAddress($address): array
    {
        $addressData = [];

        if (!empty($address)) {
            $addressData = $address->getData();
            if (isset($addressData['street']) && !\is_array($address['street'])) {
                $addressData['street'] = explode("\n", $addressData['street']);
            }
            $countryId = $addressData['country_id'] ?? null;
            $addressData['country'] = $this->countryFactory->create()->loadByCode($countryId)->getName();
        }

        return $addressData;
    }

    /**
     * Get and mapping company account custom attributes and assign to return data
     *
     * @param array $customerData
     */
    private function mappingCompanyAccountCustomAttributes(&$customerData)
    {
        try {
            $customerAttributes = [];
            $customer = $this->customerRepository->getById($customerData['company_account_id']);

            foreach ($customer->getCustomAttributes() as $attribute) {
                $customerData[$attribute->getAttributeCode()] = $attribute->getValue();
                $customerAttribute = [
                    'attribute_code' => $attribute->getAttributeCode(),
                    'value' => $attribute->getValue()
                ];
                $customerAttributes[] = $customerAttribute;
            }
            $customerData['company_account_custom_attributes'] = $customerAttributes;

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
