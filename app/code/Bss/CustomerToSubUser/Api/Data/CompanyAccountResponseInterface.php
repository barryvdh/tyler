<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Api\Data;

/**
 * Interface CompanyAccountResponseInterface
 */
interface CompanyAccountResponseInterface
{
    const SUB_USER = 'sub_user';
    const COMPANY_CUSTOMER = 'company_account';

    /**
     * Get sub-user data
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUser();

    /**
     * Get company account
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCompanyCustomer();

    /**
     * Set sub-user data
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $user
     * @return $this
     */
    public function setSubUser(\Bss\CompanyAccount\Api\Data\SubUserInterface $user);

    /**
     * Set company customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     */
    public function setCompanyCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer);
}
