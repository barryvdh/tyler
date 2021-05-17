<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Plugin\Ui\DataProvider;

use Bss\ProductSkuPrefix\Helper\ConfigProvider;
use Bss\ProductSkuPrefix\Model\ResourceModel\GetProductEntityNextIncrementValue;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\General as BePlugged;

/**
 * Class ModifySku
 * Set prefix sku for create new page
 */
class ModifySku
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var GetProductEntityNextIncrementValue
     */
    protected $getProductEntityNextIncrementValue;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * ModifySku constructor.
     *
     * @param ConfigProvider $configProvider
     * @param GetProductEntityNextIncrementValue $getProductEntityNextIncrementValue
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        ConfigProvider $configProvider,
        GetProductEntityNextIncrementValue $getProductEntityNextIncrementValue,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->configProvider = $configProvider;
        $this->getProductEntityNextIncrementValue = $getProductEntityNextIncrementValue;
        $this->request = $request;
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
        $prefix = $this->configProvider->getProductTypePrefix(
            $productType
        );
        if ($productId || $prefix === false || !$this->configProvider->isEnable()) {
            return $meta;
        }

        if (isset($meta["product-details"]["children"]["container_sku"]["children"]
            ["sku"]["arguments"]["data"]["config"])
        ) {
            $skuConfig = &$meta["product-details"]["children"]["container_sku"]["children"]
            ["sku"]["arguments"]["data"]["config"];
            $uniqueSkuNum = $this->getProductEntityNextIncrementValue->execute();
            $skuConfig['prefixSku'] = $prefix . $uniqueSkuNum;
            $skuConfig['component'] = 'Bss_ProductSkuPrefix/js/components/view/prefix-sku';
        }

        return $meta;
    }
}
