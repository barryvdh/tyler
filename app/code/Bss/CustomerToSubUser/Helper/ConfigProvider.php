<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class SubUserHelper
 */
class ConfigProvider
{
    const XML_PATH_CUSTOMER_TO_SUB_USER_WELCOME_TEMPLATE = 'bss_company_account/email/convert_customer_to_sub_user';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get email template
     *
     * @return string
     */
    public function getConvertToSubUserEmailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_TO_SUB_USER_WELCOME_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }
}
