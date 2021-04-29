<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CustomerToSubUser\Api\Data\CompanyAccountResponseInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class CompanyAccountResponse - response object
 */
class CompanyAccountResponse extends AbstractSimpleObject implements CompanyAccountResponseInterface
{

    /**
     * @inheritDoc
     */
    public function getSubUser()
    {
        return $this->_get(self::SUB_USER);
    }

    /**
     * @inheritDoc
     */
    public function getCompanyCustomer()
    {
        return $this->_get(self::COMPANY_CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setSubUser(\Bss\CompanyAccount\Api\Data\SubUserInterface $user)
    {
        return $this->setData(self::SUB_USER, $user);
    }

    /**
     * @inheritDoc
     */
    public function setCompanyCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return $this->setData(self::COMPANY_CUSTOMER, $customer);
    }
}
