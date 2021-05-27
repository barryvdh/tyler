<?php

/*
 * This file will declare and create your custom table
 */

namespace FME\Restrictcustomergroup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        // Get v table
        $tableName = $installer->getTable('fme_restrictcustomergroup_rule');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create Restrictcustomergroup table
            $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                        'rule_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'title',
                        Table::TYPE_TEXT,
                        null,
                        [
                        'nullable' => false, 'default' => ''
                        ],
                        'Title'
                    )
                    ->addColumn(
                        'priority',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                        'nullable' => false,
                        'default' => 0,
                        ],
                        'priority'
                    )
                    ->addColumn(
                        'cms_page_ids',
                        Table::TYPE_TEXT,
                        null,
                        [
                        'nullable' => false,
                        'default' => ''
                        ],
                        'CMS Page Ids'
                    )
                    ->addColumn('conditions_serialized', Table::TYPE_TEXT, '2M', [], 'Rule conditions')
                    ->addColumn('url_serialized', Table::TYPE_TEXT, '2M', [], 'Serialized URLs')
                    ->addColumn('error_msg', Table::TYPE_TEXT, '2M', [], 'Error Message')
                    ->addColumn(
                        'creation_time',
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                        'nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Item Creation Time'
                    )->addColumn(
                        'update_time',
                        Table::TYPE_TIMESTAMP,
                        null,
                        [
                        'nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                        'Item Modification Time'
                    )
                    ->addColumn(
                        'rule_type',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                        'nullable' => false, 'default' => '0'
                        ],
                        'Rule Type'
                    )
                    ->addColumn(
                        'is_active',
                        Table::TYPE_SMALLINT,
                        null,
                        [
                        'nullable' => false, 'default' => '0'
                        ],
                        'Status'
                    )
                    ->setComment('main Table')
                    ->setOption('type', 'InnoDB')
                    ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        /**
         * Create table 'fme_restrictcustomergroup_customer_group'
         */
        $tableCustomerGroup = $installer->getTable('fme_restrictcustomergroup_customer_group');
        if ($installer->getConnection()->isTableExists($tableCustomerGroup) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableCustomerGroup)
                    ->addColumn(
                        'rule_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                                'identity' => true,
                                'unsigned' => true,
                                'nullable' => false,
                                'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'customer_group_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                        ],
                        'Customer Group Id'
                    )
                    ->addIndex(
                        $installer->getIdxName('fme_restrictcustomergroup_customer_group', ['customer_group_id']),
                        ['customer_group_id']
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'fme_restrictcustomergroup_customer_group',
                            'rule_id',
                            'fme_restrictcustomergroup_rule',
                            'rule_id'
                        ),
                        'rule_id',
                        $installer->getTable('fme_restrictcustomergroup_rule'),
                        'rule_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    );
            $installer->getConnection()->createTable($table);
        }

        $tableBlocks = $installer->getTable('fme_restrictcustomergroup_block');
        /**
         * Create table 'fme_restrictcustomergroup_block'
         */
        if ($installer->getConnection()->isTableExists($tableBlocks) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableBlocks)
                    ->addColumn(
                        'rule_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                            'identity' => true,
                            'unsigned' => true,
                            'nullable' => false,
                            'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'block_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [

                            'nullable' => false,
                            'primary' => true
                        ],
                        'Block ID'
                    )
                    ->addIndex(
                        $installer->getIdxName('fme_restrictcustomergroup_block', ['block_id']),
                        ['block_id']
                    )->addForeignKey(
                        $installer->getFkName(
                            'fme_restrictcustomergroup_block',
                            'rule_id',
                            'fme_restrictcustomergroup_rule',
                            'rule_id'
                        ),
                        'rule_id',
                        $installer->getTable('fme_restrictcustomergroup_rule'),
                        'rule_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'fme_restrictcustomergroup_block',
                            'block_id',
                            'cms_block',
                            'block_id'
                        ),
                        'block_id',
                        $installer->getTable('cms_block'),
                        'block_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )->setComment('FME Restrictcustomergroup To Static Block Linkage Table');
            $installer->getConnection()->createTable($table);
        }

        $tableStore = $installer->getTable('fme_restrictcustomergroup_store');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableStore) != true) {
            /**
             * Create table 'fme_geoipultimatelock_store'
             */
            $table = $installer->getConnection()
                    ->newTable($tableStore)
                    ->addColumn(
                        'rule_id',
                        Table::TYPE_INTEGER,
                        null,
                        [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                        ],
                        'ID'
                    )
                    ->addColumn(
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                        ],
                        'Store ID'
                    )
                    ->addIndex(
                        $installer->getIdxName('fme_geoipultimatelock_store', ['store_id']),
                        ['store_id']
                    )->addForeignKey(
                        $installer->getFkName(
                            'fme_restrictcustomergroup_store',
                            'rule_id',
                            'fme_restrictcustomergroup_rule',
                            'rule_id'
                        ),
                        'rule_id',
                        $installer->getTable('fme_restrictcustomergroup_rule'),
                        'rule_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )->addForeignKey(
                        $installer->getFkName(
                            'fme_restrictcustomergroup_store',
                            'store_id',
                            'store',
                            'store_id'
                        ),
                        'store_id',
                        $installer->getTable('store'),
                        'store_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                    )->setComment(
                        'FME Restrictcustomergroup To Store Linkage Table'
                    );

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
