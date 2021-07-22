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

namespace Bss\ProductInventoryReport\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Drop column period
 */
class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Version of module in setup table is less then the give value.
        // Neu phien ban module trong db nho hon phien ban trong code
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            // drop period col
            $dailyTbl = $setup->getTable(InstallSchema::TBL_INVENTORY_REPORT_DAILY);
            if ($setup->getConnection()->isTableExists($dailyTbl) == true) {
                $connection = $setup->getConnection();
                $connection->dropColumn($dailyTbl, 'period');
                $connection->addColumn(
                    $dailyTbl,
                    "product_type",
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 128,
                        'nullable' => true,
                        'after' => 'product_id',
                        'comment' => 'Product type id'
                    ]
                );
            }

            // Drop not used table
            $tables = [
                'monthly' => InstallSchema::TBL_INVENTORY_REPORT_MONTHLY,
                'yearly' => InstallSchema::TBL_INVENTORY_REPORT_YEARLY
            ];
            foreach ($tables as $tbl) {
                $tbl = $setup->getTable($tbl);
                $setup->getConnection()->dropTable($tbl);
            }
        }
    }
}
