<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\ViewModel;

/**
 * Class UseCompanyConfig
 */
class UseCompanyConfig implements \Magento\Framework\Data\OptionSourceInterface
{
    const USE_COMPANY_CATEGORY_CONFIG = 1;
    const CUSTOMIZE_PER_BRAND_CATEGORY = 0;

    /**
     * Return the field's options array
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => __('Use Company Category Configuration'),
                'value' => self::USE_COMPANY_CATEGORY_CONFIG
            ],
            [
                'label' => __('Customize per Brand Category'),
                'value' => self::CUSTOMIZE_PER_BRAND_CATEGORY
            ]
        ];
    }
}
