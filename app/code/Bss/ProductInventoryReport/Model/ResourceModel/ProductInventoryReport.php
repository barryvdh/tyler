<?php
declare(strict_types=1);
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
 * @package    Bss_ProductInventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductInventoryReport\Model\ResourceModel;

use Bss\BrandRepresentative\Model\BrandProcessor;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Bss\ProductInventoryReport\Model\Flag;
use Bss\ProductInventoryReport\Setup\InstallSchema;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Psr\Log\LoggerInterface;

/**
 * Class ProductInventoryReport
 * Bss\ProductInventoryReport\Model\ResourceModel\ProductInventoryReport
 */
class ProductInventoryReport extends AbstractReport
{
    const AGGREGATION_DAILY = InstallSchema::TBL_INVENTORY_REPORT_DAILY;
    const BRAND_LV = 3;

    /**
     * @var array|null
     */
    protected $brandIdData;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var BrandProcessor
     */
    protected $brandProcessor;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param BrandProcessor $brandProcessor
     * @param string|null $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        CollectionFactory $productCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        BrandProcessor $brandProcessor,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockRegistry = $stockRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->brandProcessor = $brandProcessor;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aggregate($from = null, $to = null)
    {
        $startTime = microtime(true);
        $connection = $this->getConnection();

        try {
            $this->truncateTable();
            $insertBatches = [];
            $collection = $this->productCollectionFactory->create();
            $collection->addFieldToSelect(['name', 'status']);

            //Convert Collection to correct insert batches
            /** @var Product $product */
            foreach ($collection as $product) {
                $batch = [
                    'product_id'    => $product->getId(),
                    'product_type'  => $product->getTypeId(),
                    'product_sku'   => $product->getSku(),
                    'product_name'  => $product->getName(),
                    'status'        => $product->getStatus()
                ];

                $stockItem = $this->stockRegistry->getStockItem($product->getId());

                $batch['stock_status'] = (int) $stockItem->getIsInStock();
                $batch['brand_id'] = $this->getBrandId($product);
                $batch['max_order_amount'] = $stockItem->getMaxSaleQty();
                $batch['inventory_qty'] = $stockItem->getQty();

                $insertBatches[] = $batch;
            }

            $tableName = $connection->getTableName(self::AGGREGATION_DAILY);

            //Break down array to prevent large data query, heap size excess
            foreach (array_chunk($insertBatches, 100) as $batch) {
                $connection->insertMultiple($tableName, $batch);
            }

            $this->_setFlagData(Flag::REPORT_INVENTORY_REPORT_FLAG_CODE);
            $this->_logger->info(
                __(
                    "BSS - Update aggregate table time: %1 second for %2 record(s)",
                    round(microtime(true) - $startTime, 4),
                    count($insertBatches)
                )
            );
        } catch (Exception $e) {
            //If exception, truncate all report table
            $this->truncateTable();
            $this->_logger->critical($e);
            throw new CouldNotSaveException(__("Could not save report to aggregate table. Please review the log!"));
        }

        return $this;
    }

    /**
     * Get brand id
     *
     * @param Product $product
     * @return int|null
     */
    protected function getBrandId(Product $product): ?int
    {
        if ($brand = $this->brandProcessor->getBrand($product)) {
            return (int) $brand->getId();
        }

        return null;
    }

    /**
     * Clean old data before update new data
     */
    public function truncateTable()
    {
        $connection = $this->getConnection();
        $connection->truncateTable($connection->getTableName(self::AGGREGATION_DAILY));
    }
}
