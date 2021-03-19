<?php
declare(strict_types=1);
namespace Bss\CustomizeMageplazaSearch\Plugin\Helper;

/**
 * Class Data
 * Get brand category to search list
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Mageplaza\Search\Helper\Data
{
    const BRAND_LV = 3;

    /**
     * Modify the categories options
     *
     * @param \Mageplaza\Search\Helper\Data $subject
     * @param callable $proceed
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetCategoryTree(
        \Mageplaza\Search\Helper\Data $subject,
        $proceed
    ) {
        $categoriesOptions = [0 => __('All Brands')];

        $maxLevel   = 2;
        $parent     = $this->storeManager->getStore()->getRootCategoryId();
        $categories = $this->categoryFactory->create()
            ->getCategories($parent, 1, false, true);
        foreach ($categories as $category) {
            $this->getCategoryOptions($category, $categoriesOptions, $maxLevel);
        }

        return $categoriesOptions;
    }

    /**
     * Get sub-brand category options
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param array $options
     * @param int $level
     * @param string $htmlPrefix
     * @return $this
     */
    protected function getCategoryOptions($category, &$options, $level, $htmlPrefix = '')
    {
        if ($level <= 0) {
            return $this;
        }
        $level--;

        if ($category->getLevel() == self::BRAND_LV) {
            $options[$category->getId()] = $htmlPrefix . $category->getName();
        }

        foreach ($this->getChildCategories($category) as $childCategory) {
            $this->getCategoryOptions($childCategory, $options, $level);
        }

        return $this;
    }
}
