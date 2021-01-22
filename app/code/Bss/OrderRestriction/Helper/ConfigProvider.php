<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider
{
    const ENABLE_CONFIG_XML_PATH = "bss_order_restriction/general/enable";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is module enabled
     *
     * @param int||null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE_CONFIG_XML_PATH,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }
}
