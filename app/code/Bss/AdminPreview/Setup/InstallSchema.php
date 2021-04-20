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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Login as customer setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bss_login_as_customer'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bss_adminpreview_login_as_customer')
        )->addColumn(
            'login_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Admin Login ID'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Customer ID'
        )->addColumn(
            'admin_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => true],
            'Admin ID'
        )->addColumn(
            'secret',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            '64',
            ['nullable' => true],
            'Login Secret'
        )->addColumn(
            'used',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Is Login Used'
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Creation Time'
        )->addIndex(
            $installer->getIdxName('bss_adminpreview_login_as_customer', ['customer_id']),
            ['customer_id']
        )
            ->addIndex(
                $installer->getIdxName('bss_adminpreview_login_as_customer', ['admin_id']),
                ['admin_id']
            )->setComment(
                'Bss AdminPreview Login As Customer Table'
            );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'items_ordered',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'comment' => 'Items Ordered'
            ]
        );

        $installer->endSetup();
    }
}
