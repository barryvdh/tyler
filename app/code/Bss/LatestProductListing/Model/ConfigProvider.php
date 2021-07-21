<?php
declare(strict_types=1);
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
 * @package    Bss_LatestProductListing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\LatestProductListing\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provide module configuration
 */
class ConfigProvider
{
    const PERIOD_TIME_XML_PATH = "latest_product_listing/general/period";

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     *
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        ScopeConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Get config period time
     *
     * @param mixed $store
     * @return Int
     */
    public function getPeriodTime($store = null): Int
    {
        $configValue = $this->config->getValue(
            self::PERIOD_TIME_XML_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($configValue) {
            return (int) $configValue;
        }

        return 30;
    }
}
