<?php
declare(strict_types=1);

namespace Bss\CustomizeCompanyAccount\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * Compare sub-user login
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Add log as sub-user
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $connection = $setup->getConnection();
            $connection->addColumn(
                $setup->getTable('bss_adminpreview_login_as_customer'),
                'sub_user_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Log as Sub-user'
                ]
            );
        }
    }
}
