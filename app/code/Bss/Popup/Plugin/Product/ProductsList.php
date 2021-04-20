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

namespace Bss\Popup\Plugin\Product;

class ProductsList
{
    /**
     * PageFactory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * ProductsList constructor.
     *
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param null $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function aroundGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        callable $proceed,
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        try {
            if (!isset($arguments['zone'])) {
                $arguments['zone'] = $renderZone;
            }
            $arguments['price_id'] = isset($arguments['price_id'])
                ? $arguments['price_id']
                : 'old-price-' . $product->getId() . '-' . $priceType;
            $arguments['include_container'] = isset($arguments['include_container'])
                ? $arguments['include_container']
                : true;
            $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
                ? $arguments['display_minimal_price']
                : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
            $priceRender = $subject->getLayout()->getBlock('product.price.render.default');

            if (!$priceRender) {
                $resultPage = $this->resultPageFactory->create();
                $priceRender = $resultPage->getLayout()->getBlock('product.price.render.default');
            }

            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
            return $price;
        } catch (\Exception $e) {
            return $proceed($product, $priceType, $renderZone, $arguments);
        }
    }
}