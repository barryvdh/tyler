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

class EventDisplay implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Event values
     */
    const PAGE_LOAD = 1;
    const PAGE_SCROLL = 2;
    const VIEW_PAGE = 3;
    const IMMEDIATE = 4;
    const EXIT_INTENT = 5;

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
            ['value' => self::PAGE_LOAD,  'label' => __('After customers spend X seconds on page')],
            ['value' => self::PAGE_SCROLL,  'label' => __('After customers scroll page by X percent')],
            ['value' => self::VIEW_PAGE,  'label' => __('After customers view X pages')],
            ['value' => self::IMMEDIATE,  'label' => __('Immediately when customers visit page')],
            ['value' => self::EXIT_INTENT,  'label' => __('Exit Intent')]
        ];
    }
}
