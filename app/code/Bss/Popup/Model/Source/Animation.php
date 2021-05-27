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

class Animation implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Animation values
     */
    const NONE = 0;
    const ZOOM = 1;
    const HORIZONTAL = 2;
    const FROM_TOP = 3;
    const UNFOLD_3D = 4;
    const ZOOM_OUT = 5;

    /**
     * Get Option Array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $optionArray = ['' => ' '];
        foreach ($this->toOptionArray() as $option) {
            $optionArray[$option['value']] = $option['label'];
        }
        return $optionArray;
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE,  'label' => __('None')],
            ['value' => self::ZOOM,  'label' => __('Zoom')],
            ['value' => self::HORIZONTAL,  'label' => __('Horizontal Move')],
            ['value' => self::FROM_TOP,  'label' => __('Move from top')],
            ['value' => self::UNFOLD_3D,  'label' => __('3D Unfold')],
            ['value' => self::ZOOM_OUT,  'label' => __('Zoom-out')]
        ];
    }
}
