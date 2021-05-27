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
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepository
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->assetRepository = $assetRepository;
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

    /**
     * Get default brand cover image path
     *
     * @return string
     */
    public function getDefaultBrandCover()
    {
        return $this->assetRepository->getUrl("Bss_CategoryAttributes::images/default-brand-cover.jpg");
    }
}
