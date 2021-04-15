<?php
declare(strict_types=1);
namespace Bss\CustomizeProductStockAlert\Model\Observer;

use Bss\ProductStockAlert\Model\Observer\ApplyProductAlertOnCollectionAfterLoadObserver as BeRewritten;

/**
 * Class ApplyProductAlertOnCollectionAfterLoadObserverOverride
 * Fix bug error get stock item
 */
class ApplyProductAlertOnCollectionAfterLoadObserverOverride extends BeRewritten
{
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * ApplyProductAlertOnCollectionAfterLoadObserverOverride constructor.
     *
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
        parent::__construct($helper, $httpContext, $multiSourceInventoryHelper, $stockItemRepository);
    }

    /**
     * Check product salable
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    protected function checkProductSaleable($product)
    {
        if ($product->isAvailable()) {
            if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual') {
                $saleQty = $saleQtyNumber = $this->getSalableQty($product);
                if (is_array($saleQty) && isset($saleQty["0"]["qty"])) {
                    $saleQtyNumber = $saleQty["0"]["qty"];
                }
                if ($saleQtyNumber <= 0) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Get salable qty
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|float|array
     */
    private function getSalableQty($product)
    {
        $getSalableQuantityDataBySku = $this->multiSourceInventoryHelper->getSalableQuantityDataBySkuObject();
        if ($getSalableQuantityDataBySku &&
            $getSalableQuantityDataBySku instanceof \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku) {
            return $getSalableQuantityDataBySku->execute($product->getSku());
        }
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        return $stockItem->getQty();
    }
}
