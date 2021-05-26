<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Plugin\Controller\Product;

use Bss\ProductSkuPrefix\Helper\ConfigProvider;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class SetPrefixSku
 * Set prefix sku for product before save
 */
class SetPrefixSku
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * SetPrefixSku constructor.
     *
     * @param ConfigProvider $configProvider
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigProvider $configProvider,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->productRepository = $productRepository;
    }

    /**
     * Set prefix sku
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Save $subject
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function beforeExecute($subject)
    {
        $productId = $subject->getRequest()->getParam('id');
        $productType = $subject->getRequest()->getParam(
            'type',
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        );

        if ($productId) {
            try {
                $productType = $this->productRepository->getById($productId)->getTypeId();
            } catch (\Exception $e) {
                return;
            }
        }

        if ($subject->getRequest()->getPost('is_downloadable') === "1") {
            $productType = \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE;
        } elseif ($productType === \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE &&
            !$subject->getRequest()->getPost('is_downloadable')
        ) {
            $productData = $subject->getRequest()->getPost('product');
            if (isset($productData) && $productData['product_has_weight'] === '1') {
                // simple
                $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
            }
        }

        $params = $subject->getRequest()->getPostValue();
        $prefix = $this->configProvider->getProductTypePrefix(
            $productType
        );
        if ($prefix === false || !$this->configProvider->isEnable()) {
            return;
        }

        if (isset($params['product'])) {
            $productRequestData = &$params['product'];
            $editable = $this->configProvider->isEditable($productType);
            if ((!$editable && !$productId) || empty($productRequestData['sku'])
            ) {
                $productRequestData['sku'] = $prefix . time();
            }
            $subject->getRequest()->setPostValue($params);
        }
    }
}
