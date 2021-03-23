<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Model;

class Config
{
    /**
     * Description
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
        $options['created_at'] = __("Newest");

        return $options;
    }
}
