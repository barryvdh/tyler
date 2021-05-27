<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\ProductGridInlineEditor\Model;

use Magento\CurrencySymbol\Model\System\Currencysymbol as CoreCurrencysymbol;
use Magento\Directory\Model\Currency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Custom currency symbol model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class CurrencySymbol extends CoreCurrencysymbol
{
    /**
     * Return allowed currencies
     *
     * @return array
     */
    protected function getAllowedCurrencies()
    {
        $allowedCurrencies = explode(
            self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR,
            $this->_scopeConfig->getValue(
                self::XML_PATH_ALLOWED_CURRENCIES,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
        );

        $storeModel = $this->_systemStore;
        /** @var \Magento\Store\Model\Website $website */
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            /** @var \Magento\Store\Model\Group $group */
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                /** @var \Magento\Store\Model\Store $store */
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        $websiteSymbols = $website->getConfig(self::XML_PATH_ALLOWED_CURRENCIES);
                        $this->mergeArray($allowedCurrencies, $websiteSymbols);
                        // for config base currency
                        $websiteSymbols_base = $website->getConfig(Currency::XML_PATH_CURRENCY_BASE);
                        $this->mergeArray($allowedCurrencies, $websiteSymbols_base);
                    }
                    $storeSymbols = $this->_scopeConfig->getValue(
                        self::XML_PATH_ALLOWED_CURRENCIES,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $store
                    );
                    $this->mergeArray($allowedCurrencies, $storeSymbols);
                }
            }
        }
        return array_unique($allowedCurrencies);
    }

    /**
     * @param $allowedCurrencies
     * @param $symbols
     */
    private function mergeArray(&$allowedCurrencies, $symbols)
    {
        $allowedCurrencies = array_merge(
            $allowedCurrencies,
            explode(self::ALLOWED_CURRENCIES_CONFIG_SEPARATOR, $symbols)
        );
    }
}
