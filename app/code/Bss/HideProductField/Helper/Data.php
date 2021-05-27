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

use Magento\Cms\Model\PageFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Data
 *
 * @package Bss\HideProductField\Helper
 */
class Data extends AbstractHelper
{
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
}