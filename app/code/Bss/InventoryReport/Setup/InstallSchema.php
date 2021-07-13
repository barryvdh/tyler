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
 * @package    Bss_InventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\InventoryReport\Setup;

use Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InstallSchema
 * Bss\InventoryReport\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const TBL_INVENTORY_REPORT_DAILY = 'inventory_report_aggregated_daily';
    const TBL_INVENTORY_REPORT_MONTHLY = 'inventory_report_aggregated_monthly';
    const TBL_INVENTORY_REPORT_YEARLY = 'inventory_report_aggregated_yearly';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * InstallSchema constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Create db table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @suppressWarnings("UnusedFormalParameter")
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->createTblMyCustomReportAggregated($setup);

        $setup->endSetup();
    }

    /**
     * Create aggregate table
     *
     * @param SchemaSetupInterface $installer
     */
    protected function createTblMyCustomReportAggregated(SchemaSetupInterface $installer)
    {
        $tablesToCreate = [
            'daily' => self::TBL_INVENTORY_REPORT_DAILY,
            'monthly' => self::TBL_INVENTORY_REPORT_MONTHLY,
            'yearly' => self::TBL_INVENTORY_REPORT_YEARLY
        ];
        try {
            foreach ($tablesToCreate as $key => $tbl) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable($tbl)
                )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Id'
                )->addColumn(
                    'period',
                    Table::TYPE_DATE,
                    null,
                    [],
                    'Period'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Product Id'
                )->addColumn(
                    'product_sku',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Product SKU'
                )->addColumn(
                    'product_name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Product Name'
                )->addColumn(
                    'status',
                    Table::TYPE_SMALLINT,
                    6
                )->addColumn(
                    'stock_status',
                    Table::TYPE_SMALLINT,
                    6
                )->addColumn(
                    'brand_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Brand ID'
                )->addColumn(
                    'max_order_amount',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'how many can a customer order at one time'
                )->addColumn(
                    'inventory_qty',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Inventory Quantity'
                )->addIndex(
                    $installer->getIdxName($tbl, ['product_id']),
                    ['product_id']
                )->setComment(
                    'Inventory Report Aggregated ' . ucfirst($key)
                );

                $installer->getConnection()->createTable($table);
            }
        } catch (Exception $e) {
            $this->logger->critical('Something went wrong while creating aggregate table!' . $e->getMessage());
        }
    }
}
