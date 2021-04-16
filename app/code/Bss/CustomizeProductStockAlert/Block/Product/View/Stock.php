<?php
declare(strict_types=1);
namespace Bss\CustomizeProductStockAlert\Block\Product\View;

/**
 * Class Stock
 * Fix bug undefined var
 */
class Stock extends \Bss\ProductStockAlert\Block\Product\View\Stock
{
    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * Stock constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Bss\ProductStockAlert\Helper\MultiSourceInventory $multiSourceInventoryHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        parent::__construct(
            $storeManager,
            $multiSourceInventoryHelper,
            $context,
            $helper,
            $stockRegistry,
            $registry,
            $data
        );
    }

    /**
     * Check status
     *
     * @param int $productId
     * @param string $productSku
     * @return bool|float|int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkStatusByStockId($productId, $productSku)
    {
        $websiteId = $this->_storeManager->getWebsite()->getCode();
        $stockResolver = $this->multiSourceInventoryHelper->getStockResolverObject();
        $salableQty = $this->multiSourceInventoryHelper->getSalableQtyObject();
        if ($stockResolver && $stockResolver instanceof \Magento\InventorySalesApi\Api\StockResolverInterface &&
            $salableQty && $salableQty instanceof \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface) {
            $stockId = $stockResolver->execute('website', $websiteId)->getStockId();
        }
        $stock = $this->stockRegistry->getStockItem($productId);
        if (!isset($stockId) || !$stockId) {
            return $stock->getIsInStock();
        }
        return $salableQty->execute($productSku, (int) $stockId);
    }
}
