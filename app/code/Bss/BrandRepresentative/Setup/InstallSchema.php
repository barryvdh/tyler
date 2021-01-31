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
use Bss\BrandRepresentative\Model\ResourceModel\MostViewed as MostViewedResource;
use Bss\BrandRepresentative\Api\Data\MostViewedInterface as MostViewed;
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
            ->newTable($installer->getTable('bss_brandsales_report'))
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
                'store_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Store Id'
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
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false
                ],
                'Product Id'
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
                'brand',
                Table::TYPE_TEXT,
                128,
                [
                    'nullable' => true
                ],
                'Brand'
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
            )->addIndex(
                $installer->getIdxName(
                    'bss_brandsales_report',
                    ['store_id']
                ),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    'bss_brandsales_report',
                    ['order_id']
                ),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(
                    $installer->getTable('bss_brandsales_report'),
                    'order_id',
                    $installer->getTable('sales_order'),
                    'entity_id'
                ),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Bss Brand Order Report Table');
        $installer->getConnection()->createTable($table);

        $this->createMostViewCategoryTbl($setup);
        $installer->endSetup();
    }

    /**
     * Create most viewed category table
     *
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function createMostViewCategoryTbl(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $table = $installer->getConnection()
            ->newTable($installer->getTable(MostViewedResource::TABLE))
            ->addColumn(
                MostViewed::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Most viewed item id'
            )->addColumn(
                MostViewed::CATEGORY_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'The Category (Brand) Id'
            )->addColumn(
                MostViewed::TRAFFIC,
                Table::TYPE_BIGINT,
                null,
                [
                    'unsigned' => true,
                    'default' => 0
                ],
                'Traffic value - to help us know which brands are most interested in'
            )->addForeignKey(
                $installer->getFkName(
                    MostViewedResource::TABLE,
                    MostViewed::CATEGORY_ID,
                    'catalog_category_entity',
                    'entity_id'
                ),
                MostViewed::CATEGORY_ID,
                $installer->getTable('catalog_category_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment('Bss Most Viewed Brands Table');
        $installer->getConnection()->createTable($table);
    }
}
