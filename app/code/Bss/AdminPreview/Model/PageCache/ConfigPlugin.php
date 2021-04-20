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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Model\PageCache;

/**
 * Page cache config plugin
 */
class ConfigPlugin
{
    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * Initialize dependencies.
     *
     * @param \Bss\AdminPreview\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Bss\AdminPreview\Helper\Data $helper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    )
    {
        $this->helper = $helper;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Disable page cache if needed when admin is logged as customer
     *
     * @param \Magento\PageCache\Model\Config $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsEnabled(\Magento\PageCache\Model\Config $subject, $result)
    {
        if ($result) {
            $adminLogged = $this->cookieManager->getCookie('adminLogged');
            $disable = $this->helper->isDisablePageCache();
            $moduleEnable = $this->helper->isEnable();
            if ($adminLogged && $disable && $moduleEnable) {
                $result = false;
            }
        }
        return $result;
    }
}
