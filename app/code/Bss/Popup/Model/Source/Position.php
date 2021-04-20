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

class Position implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Position values
     */
    const TOP_LEFT = 1;
    const TOP_CENTER = 2;
    const TOP_RIGHT = 3;
    const MIDDLE_LEFT = 4;
    const MIDDLE_CENTER = 5;
    const MIDDLE_RIGHT = 6;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TOP_LEFT,  'label' => __('Top Left')],
            ['value' => self::TOP_CENTER,  'label' => __('Top Center')],
            ['value' => self::TOP_RIGHT,  'label' => __('Top Right')],
            ['value' => self::MIDDLE_LEFT,  'label' => __('Middle Left')],
            ['value' => self::MIDDLE_CENTER,  'label' => __('Middle Center')],
            ['value' => self::MIDDLE_RIGHT,  'label' => __('Middle Right')],
        ];
    }
}
