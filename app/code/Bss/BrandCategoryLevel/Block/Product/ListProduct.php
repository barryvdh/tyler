<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Block\Product;

/**
 * Class ListProduct
 * Customize for brand display
 */
class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    const BRAND_CATEGORY_LV = 3;

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    public function getLoadedProductCollection()
    {
        $currentCategory = $this->getLayer()->getCurrentCategory();

        if ($currentCategory->getLevel() != static::BRAND_CATEGORY_LV) {
            return parent::getLoadedProductCollection();
        }

        $collection = $this->_getProductCollection();
        $categoryId = $currentCategory->getId();
        foreach ($collection as $product) {
            if ($product->getCategoryId() != $categoryId) {
                $collection->removeItemByKey($product->getId());
            }
        }

        return $collection;
    }
}
