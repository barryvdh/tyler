<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Get module config
 */
class ConfigProvider
{
    const CONFIG_POPUP_OPTIONS_XML_PATH = "brand_contact/popup_options";

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
     * Get popup options
     *
     * @param int|null $storeView
     * @return array
     */
    public function getPopupOptions($storeView = null)
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_POPUP_OPTIONS_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeView
        );
    }
}
