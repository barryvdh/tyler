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
    const SALE_QTY_PER_MONTH_CONFIG_XML_PATH = "cataloginventory/item_options/sale_qty_per_month";
    const ORDER_RESTRICTION_ENABLED_XML_PATH = "bss_order_restriction/general/enable";

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
     * @param null|int $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ORDER_RESTRICTION_ENABLED_XML_PATH,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * Get default sale Qty per month value
     *
     * @param int $store
     * @return int|null
     */
    public function getDefaultSaleQtyValue($store = null)
    {
        return $this->scopeConfig->getValue(
            self::SALE_QTY_PER_MONTH_CONFIG_XML_PATH,
            ScopeInterface::SCOPE_STORES,
            $store
        );
    }
}
