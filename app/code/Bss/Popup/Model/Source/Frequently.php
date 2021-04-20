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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Model\Source;

class Frequently implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Frequently values
     */
    const EVERY_TIME = 1;
    const ONLY_ONCE = 2;
    const COOKIE_EXPIRES = 3;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EVERY_TIME,  'label' => __('When all conditions are satisfied')],
            ['value' => self::ONLY_ONCE,  'label' => __('Only once')],
            ['value' => self::COOKIE_EXPIRES,  'label' => __('Only once per session')]
        ];
    }
}
