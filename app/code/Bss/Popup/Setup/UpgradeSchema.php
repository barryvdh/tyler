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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), "1.1.1", "<")) {
            $installer = $setup;
            $installer->startSetup();
            $table_bss_popup_layout = $installer->getConnection()->newTable($setup->getTable('bss_popup_layout'));

            $table_bss_popup_layout->addColumn(
                'layout_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                'Entity ID'
            );

            $table_bss_popup_layout->addColumn(
                'popup_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'popup_id'
            );

            $table_bss_popup_layout->addColumn(
                'page_group',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'page_group'
            );

            $table_bss_popup_layout->addColumn(
                'layout_handle',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'layout_handle'
            );

            $table_bss_popup_layout->addColumn(
                'page_for',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'page_for'
            );

            $table_bss_popup_layout->addColumn(
                'entities',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'entities'
            );

            $table_bss_popup_layout->addIndex(
                $installer->getIdxName('bss_popup_layout', ['layout_id']),
                ['layout_id']
            );
            $installer->getConnection()->createTable($table_bss_popup_layout);
            $table_bss_popup_layout_update = $installer->getConnection()->newTable($setup->getTable('bss_popup_layout_update'));

            $table_bss_popup_layout_update->addColumn(
                'layout_update_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true,],
                'Entity ID'
            );

            $table_bss_popup_layout_update->addColumn(
                'layout_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'layout_id'
            );

            $table_bss_popup_layout_update->addColumn(
                'popup_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'popup_id'
            );

            $table_bss_popup_layout_update->addColumn(
                'handle',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                [],
                'handle'
            );

            $table_bss_popup_layout_update->addIndex(
                $installer->getIdxName('bss_popup_layout_update', ['layout_update_id']),
                ['layout_update_id']
            );
            $installer->getConnection()->createTable($table_bss_popup_layout_update);
            $installer->endSetup();
        }
        if (version_compare($context->getVersion(), "1.1.3", "<")) {
            $this->addTypeTemplate($setup);
        }
    }
    /**
     * Add Type template in table bss_popup_popup
     *
     * @param SchemaSetupInterface $setup
     */
    protected function addTypeTemplate($setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('bss_popup_popup'),
                'type_template',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Type template'
                ]
            );
    }
}
