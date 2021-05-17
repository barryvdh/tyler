<?php
declare(strict_types=1);

namespace Bss\ProductSkuPrefix\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider
{
    const XML_SKU_PREFIX_ENABLE = 'catalog/fields_masks/enable_sku_prefix';
    const XML_SKU_PREFIX_DATA = 'catalog/fields_masks/sku_prefix';

    /**
     * @var array
     */
    protected $configPrefixData = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Is customize module enable
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_SKU_PREFIX_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get serialized data
     *
     * @param int|null $storeId
     */
    public function getSerializedConfigData($storeId = null)
    {
        if (!$this->configPrefixData) {
            $values = $this->scopeConfig->getValue(
                self::XML_SKU_PREFIX_DATA,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            if ($values) {
                $this->configPrefixData = $this->serializer->unserialize($values);
            }
        }

        return $this->configPrefixData;
    }

    /**
     * Get product prefix
     *
     * @param string $productType
     * @return false|mixed
     */
    public function getProductTypePrefix($productType)
    {
        $configData = $this->getSerializedConfigData();

        foreach ($configData as $rowId => $value) {
            if (isset($value["product_type"]) && $value['product_type'] === $productType) {
                return $value["prefix"] ?? false;
            }
        }

        return false;
    }
}
