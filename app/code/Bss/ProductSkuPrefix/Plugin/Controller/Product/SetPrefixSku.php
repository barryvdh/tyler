<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Plugin\Controller\Product;

use Bss\ProductSkuPrefix\Helper\ConfigProvider;
use Bss\ProductSkuPrefix\Model\ResourceModel\GetProductEntityNextIncrementValue;
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
     * @var GetProductEntityNextIncrementValue
     */
    protected $getProductEntityNextIncrementValue;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * SetPrefixSku constructor.
     *
     * @param ConfigProvider $configProvider
     * @param GetProductEntityNextIncrementValue $getProductEntityNextIncrementValue
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        ConfigProvider $configProvider,
        GetProductEntityNextIncrementValue $getProductEntityNextIncrementValue,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configProvider = $configProvider;
        $this->getProductEntityNextIncrementValue = $getProductEntityNextIncrementValue;
        $this->productRepository = $productRepository;
    }

    /**
     * Set prefix sku
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Save $subject
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

        $params = $subject->getRequest()->getPostValue();
        $prefix = $this->configProvider->getProductTypePrefix(
            $productType
        );
        if ($prefix === false || !$this->configProvider->isEnable()) {
            return;
        }

        if (isset($params['product'])) {
            $productRequestData = &$params['product'];
            $uniqueSkuNum = $this->getProductEntityNextIncrementValue->execute();
            $editable = $this->configProvider->isEditable($productType);
            if (!$editable || empty($productRequestData['sku'])
            ) {
                $productRequestData['sku'] = $prefix . $uniqueSkuNum;
            }
            $subject->getRequest()->setPostValue($params);
        }
    }
}
