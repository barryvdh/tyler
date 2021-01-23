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
 * @package    Bss_BrandSalesReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandSalesReport\Setup;

use Exception;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class InstallSchema
 * Bss\BrandSalesReport\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    public const TBL_BRANDSALESREPORT_DAILY = 'brandsalesreport_aggregated_daily';
    public const TBL_BRANDSALESREPORT_MONTHLY = 'brandsalesreport_aggregated_monthly';
    public const TBL_BRANDSALESREPORT_YEARLY = 'brandsalesreport_aggregated_yearly';

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
     * @param SchemaSetupInterface $installer
     */
    protected function createTblMyCustomReportAggregated(SchemaSetupInterface $installer)
    {
        $tablesToCreate = [
            'daily' => self::TBL_BRANDSALESREPORT_DAILY,
            'monthly' => self::TBL_BRANDSALESREPORT_MONTHLY,
            'yearly' => self::TBL_BRANDSALESREPORT_YEARLY
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
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Order Id'
                )->addColumn(
                    'store_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true],
                    'Store Id'
                )->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Product Id'
                )->addColumn(
                    'product_sku',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product SKU'
                )->addColumn(
                    'product_name',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product Name'
                )->addColumn(
                    'product_brand',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product Brand'
                )->addColumn(
                    'product_brand_email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Product Brand Email'
                )->addColumn(
                    'qty_ordered',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Qty Ordered'
                )->addIndex(
                    $installer->getIdxName(
                        $tbl,
                        ['period', 'order_id', 'store_id', 'product_id'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['period', 'order_id', 'store_id', 'product_id'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                )->addIndex(
                    $installer->getIdxName($tbl, ['store_id']),
                    ['store_id']
                )->addIndex(
                    $installer->getIdxName($tbl, ['order_id']),
                    ['order_id']
                )->addIndex(
                    $installer->getIdxName($tbl, ['product_id']),
                    ['product_id']
                )->addForeignKey(
                    $installer->getFkName(
                        $tbl,
                        'store_id',
                        'store',
                        'store_id'
                    ),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    Table::ACTION_CASCADE
                )->setComment(
                    'Brand Sales Report Aggregated ' . ucfirst($key)
                );

                $installer->getConnection()->createTable($table);
            }
        } catch (Exception $e) {
            $this->logger->critical('Something went wrong while creating aggregate table!' . $e->getMessage());
        }
    }
}
