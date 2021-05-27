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
 * @package    Bss_AdminActionLog
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AdminActionLog\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 *
 * @package Bss\AdminActionLog\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrade
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->modifyColumn(
                $installer->getTable('bss_admin_action_detail_log'),
                'old_value',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '16M',
                    'nullable' => false,
                    'comment' => 'Old Value'
                ]
            )->modifyColumn(
                $installer->getTable('bss_admin_action_detail_log'),
                'new_value',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '16M',
                    'nullable' => false,
                    'comment' => 'New Value'
                ]
            );
        }
        $installer->endSetup();
    }
}
