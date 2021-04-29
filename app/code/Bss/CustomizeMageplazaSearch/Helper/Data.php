<?php
declare(strict_types=1);
namespace Bss\CustomizeMageplazaSearch\Helper;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * Class Data
 * Rewrite for compatible with bss admin preview
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Mageplaza\Search\Helper\Data
{
    /**
     * Rewrite mageplaza, add field to compatible with admin preview
     *
     * @param \Magento\Store\Model\Store $store
     * @param int $group
     *
     * @return $this
     */
    public function createJsonFileForStore($store, $group)
    {
        if (!$this->isEnabled($store->getId())) {
            return $this;
        }

        $productList = [];

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->objectManager->create(Collection::class);
        $collection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->setStore($store)
            ->addPriceData($group)
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInSearchIds());

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $price = $this->_priceHelper->currencyByStore(
                $product->getFinalPrice(),
                $store,
                false,
                false
            );
            $isJustForPreview = false;
            if ($isJustForPreviewAttr = $product->getCustomAttribute("bss_admin_preview")) {
                $isJustForPreview = $isJustForPreviewAttr->getValue() === "1";
            }

            $productList[] = [
                'value' => $product->getName(),
                'c'     => $product->getCategoryIds(), //categoryIds
                'd'     => $this->getProductDescription($product, $store), //short description
                'p'     => $price, //price
                'i'     => $this->getMediaHelper()->getProductImage($product),//image
                'u'     => $this->getProductUrl($product), //product url
                'just_for_preview'     => $isJustForPreview
            ];
        }

        $this->getMediaHelper()->createJsFile(
            $this->getJsFilePath($group, $store),
            'var mageplazaSearchProducts = ' . self::jsonEncode($productList)
        );

        return $this;
    }
}
