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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\BrandRepresentative\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * Bss\BrandRepresentative\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Edit main table for module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('bss_sales_report'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Report Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Order Id'
            )->addColumn(
                'product_sku',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Product Sku'
            )->addColumn(
                'product_name',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Product Name'
            )->addColumn(
                'product_type',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Product Type'
            )->addColumn(
                'ordered_qty',
                Table::TYPE_DECIMAL,
                '20,2',
                [
                    'nullable' => true
                ],
                'Item Ordered Amount'
            )->addColumn(
                'ordered_time',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Ordered Time'
            )->addColumn(
                'customer_name',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Customer Name'
            )->addColumn(
                'address',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Address'
            )
            ->addColumn(
                'address_2',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Secondary Address'
            )->addColumn(
                'city',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'City'
            )->addColumn(
                'province',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Province'
            )->addColumn(
                'representative_email',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Representative Email'
            )
            ->addColumn(
                'sent_status',
                Table::TYPE_SMALLINT,
                3,
                [
                    'nullable' => true
                ],
                'Send Status'
            )->setComment('Order Table');
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
