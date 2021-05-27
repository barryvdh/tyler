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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Model\Source;

/**
 * Class Template is type template
 */
class Template implements \Magento\Framework\Data\OptionSourceInterface
{
    const NONE=0;
    const TEMPLATE_CONTACT_FORM=1;
    const TEMPLATE_AGE_VERIFICATION=2;
    const TEMPLATE_NEWSLETTER=3;
    const TEMPLATE_HOT_DEALS=4;
    const SOCIAL_SHARING=5;
    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NONE, 'label' => __('None')],
            ['value' => self::TEMPLATE_CONTACT_FORM, 'label' => __('Template Contact form')],
            ['value' => self::TEMPLATE_AGE_VERIFICATION, 'label' => __('Template Age Verification')],
            ['value' => self::TEMPLATE_NEWSLETTER, 'label' => __('Template Newsletter')],
            ['value' => self::TEMPLATE_HOT_DEALS, 'label' => __('Template Hot deals (product listing)')],
            ['value' => self::SOCIAL_SHARING, 'label' => __('Social sharing')],
        ];
    }
}
