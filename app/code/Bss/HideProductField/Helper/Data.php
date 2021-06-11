<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_HideProductField
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HideProductField\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * Config helper data
 */
class Data extends AbstractHelper
{
    const HIDE_MEDIA_ATTRIBUTES_CONFIG_XML_PATH = "hide_field/general/media_attributes";
    const HIDE_VISIBILITY_OPTIONS_CONFIG_XML_PATH = "hide_field/general/visibility_options";

    /**
     * Get hide attributes config
     *
     * @return string
     */
    public function getAdditionalAttributeConfig()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            'hide_field/general/attributes',
            $storeScope
        );
    }

    /**
     * Is enable module
     *
     * @return bool
     */
    public function isEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(
            'hide_field/general/enable',
            $storeScope
        );
    }

    /**
     * Get hide media attributes
     *
     * @param int|null $storeId
     * @return array
     */
    public function getHideMediaAttributes($storeId = null): array
    {
        return $this->getArrayConfig(
            self::HIDE_MEDIA_ATTRIBUTES_CONFIG_XML_PATH,
            $storeId
        );
    }

    /**
     * Get hide media attributes
     *
     * @param int|null $storeId
     * @return array
     */
    public function getHideVisibilityOptions($storeId = null): array
    {
        return $this->getArrayConfig(
            self::HIDE_VISIBILITY_OPTIONS_CONFIG_XML_PATH,
            $storeId
        );
    }

    /**
     * Get array data config
     *
     * @param string $path
     * @param int|null $storeId
     * @return array|false|string[]
     */
    protected function getArrayConfig(string $path, $storeId = null)
    {
        $configData = $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($configData) {
            return explode(",", $configData);
        }

        return [];
    }
}
