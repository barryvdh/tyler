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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('bss_popup_popup')) {
            $fileTable = $installer->getConnection()->newTable($installer->getTable('bss_popup_popup'))
                ->addColumn(
                    'popup_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )
                ->addColumn(
                    'popup_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Title'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    1,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    'storeview',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    0,
                    ['nullable' => false],
                    'Store View'
                )
                ->addColumn(
                    'customer_group',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    0,
                    ['nullable' => false],
                    'Customer Group'
                )
                ->addColumn(
                    'display_from',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Display From'
                )->addColumn(
                    'display_to',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true],
                    'Display To'
                )->addColumn(
                    'priority',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Priority'
                )->addColumn(
                    'event_display',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    0,
                    ['nullable' => false],
                    'Event Display'
                )
                ->addColumn(
                    'after_load',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'After Load'
                )
                ->addColumn(
                    'after_scroll',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'After Load'
                )
                ->addColumn(
                    'page_view',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Page View'
                )
                ->addColumn(
                    'effect_display',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Effect Display'
                )
                ->addColumn(
                    'page_display',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    0,
                    ['nullable' => false],
                    'Page Display'
                )
                ->addColumn(
                    'exclude_category',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    0,
                    ['nullable' => false],
                    'Exclude Categories'
                )
                ->addColumn(
                    'exclude_product',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    0,
                    ['nullable' => false],
                    'Exclude Product'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    0,
                    ['nullable' => false],
                    'Position'
                )
                ->addColumn(
                    'hide_after',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    ['nullable' => false],
                    'Hide After'
                )
                ->addColumn(
                    'close_outside',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    0,
                    ['nullable' => false],
                    'Close Click Outside'
                )
                ->addColumn(
                    'frequently',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    0,
                    ['nullable' => false],
                    'Display Frequently'
                )
                ->addColumn(
                    'cookie_expires',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    0,
                    ['nullable' => false],
                    'Cookie Expires'
                )
                ->addColumn(
                    'popup_content',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Exclude Product'
                )
                ->addColumn(
                    'popup_css',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false],
                    'Popup Css'
                );

            $installer->getConnection()->createTable($fileTable);
            $installer->getConnection()->addIndex(
                $installer->getTable('bss_popup_popup'),
                $setup->getIdxName(
                    $installer->getTable('bss_popup_popup'),
                    ['popup_name'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['popup_name'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $installer->endSetup();
    }
}
