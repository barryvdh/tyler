<?php
declare(strict_types=1);

namespace Bss\CustomizeCompanyAccount\Block\Adminhtml\Edit\SubUser;

/**
 * Class CancelBtn
 * Set provider for cancel button on sub_user form
 */
class CancelBtn extends \Bss\CompanyAccount\Block\Adminhtml\Edit\Button\CancelButton
{
    /**
     * CancelBtn constructor.
     * Set provider for cancel button on sub_user form
     */
    public function __construct()
    {
        $this->targetName = 'customer_listing.customer_listing.'
            . 'bss_company_account_update_sub_user';
    }
}
