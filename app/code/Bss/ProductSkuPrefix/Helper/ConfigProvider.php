<?php
declare(strict_types=1);

namespace Bss\ProductSkuPrefix\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ConfigProvider
 * Get module config
 */
class ConfigProvider
{
    const XML_SKU_PREFIX_ENABLE = 'product_prefix/general/enable_sku_prefix';
    const XML_SKU_PREFIX_DATA = 'product_prefix/general/sku_prefix';

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
     * @return array
     */
    public function getSerializedConfigData(int $storeId = null): array
    {
        if (!$this->configPrefixData) {
            $values = $this->scopeConfig->getValue(
                self::XML_SKU_PREFIX_DATA,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

            try {
                if ($values) {
                    $this->configPrefixData = $this->serializer->unserialize($values);
                }
            } catch (\Exception $e) {
                $this->configPrefixData = [];
            }
        }

        return $this->configPrefixData;
    }

    /**
     * Get product prefix
     *
     * @param string $productType
     * @return false|string
     */
    public function getProductTypePrefix(string $productType)
    {
        return $this->getPrefixData($productType, "prefix");
    }

    /**
     * Get is editable
     *
     * @param string $productType
     * @return false|string
     */
    public function isEditable(string $productType)
    {
        return $this->getPrefixData($productType, "editable");
    }

    /**
     * Get prefix product type by key
     *
     * @param string $productType
     * @param string $keyData
     * @return false|string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getPrefixData(string $productType, string $keyData)
    {
        $configData = $this->getSerializedConfigData();
        foreach ($configData as $rowId => $value) {
            if (isset($value["product_type"]) && $value['product_type'] === $productType) {
                return $value[$keyData] ?? false;
            }
        }

        return false;
    }
}
