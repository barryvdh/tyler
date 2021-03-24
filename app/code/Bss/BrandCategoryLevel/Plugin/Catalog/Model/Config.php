<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Model;

/**
 * Class Config
 * Add custom sorting
 */
class Config
{
    /**
     * Add most_viewed and newest custom sorting
     *
     * @param \Magento\Catalog\Model\Config $subject
     * @param array $options
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributeUsedForSortByArray(
        \Magento\Catalog\Model\Config $subject,
        $options
    ) {
        if (isset($options['price'])) {
            unset($options['price']);
        }

        $options['most_viewed'] = __("Most Viewed");
        $options['newest'] = __("Newest");

        return $options;
    }
}
