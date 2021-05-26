<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Plugin\Ui\DataProvider;

use Bss\ProductSkuPrefix\Helper\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General as BePlugged;

/**
 * Class ModifySku
 * Set prefix sku for create new page
 */
class ModifySku
{
    const NOT_EDITABLE = [
        \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE,
        \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
    ];

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ModifySku constructor.
     *
     * @param ConfigProvider $configProvider
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigProvider $configProvider,
        \Magento\Framework\App\RequestInterface $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * Set prefix sku for create new page
     *
     * @param BePlugged $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyMeta(
        BePlugged $subject,
        $meta
    ) {
        $productId = $this->request->getParam('id', false);
        $productType = $this->request->getParam(
            'type',
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        );
        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId);
                $productType = $product->getTypeId();
            } catch (\Exception $e) {
                return $meta;
            }
        }

        if (!$this->configProvider->isEnable()) {
            return $meta;
        }

        if (isset($meta["product-details"]["children"]["container_sku"]["children"]
            ["sku"]["arguments"]["data"]["config"])
        ) {
            $skuConfig = &$meta["product-details"]["children"]["container_sku"]["children"]
            ["sku"]["arguments"]["data"]["config"];
            $skuConfig['productSku'] = isset($product) ? $product->getSku() : false;
            $skuConfig['isLoaded'] = (bool) $productId;
            $skuConfig['productType'] = $productType;
            $skuConfig['prefixData'] = $this->configProvider->getSerializedConfigData();
            $skuConfig['prefixNotice'] = __("Leave blank for auto-generated or type manual.");
            $skuConfig['component'] = 'Bss_ProductSkuPrefix/js/components/view/prefix-sku';
        }

        return $meta;
    }
}
