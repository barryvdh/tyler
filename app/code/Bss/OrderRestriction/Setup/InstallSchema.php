<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Bss\OrderRestriction\Api\Data\OrderRuleInterface as OrderRule;
use Bss\OrderRestriction\Model\ResourceModel\OrderRule as OrderRuleResource;

/**
 * Class InstallSchema - setup the bss order restriction table
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable(OrderRuleResource::TABLE))
            ->addColumn(
                OrderRule::ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                "ID"
            )
            ->addColumn(
                OrderRule::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                "Related Customer"
            )->addColumn(
                OrderRule::SALE_QTY_PER_MONTH,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                "The allowed qty/month"
            )->addColumn(
                OrderRule::USE_CONFIG,
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                "Is use the config setting"
            )->addForeignKey(
                $installer->getFkName(
                    OrderRuleResource::TABLE,
                    OrderRule::PRODUCT_ID,
                    'catalog_product_entity',
                    'entity_id'
                ),
                OrderRule::PRODUCT_ID,
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment("The Order Restriction Table");

        $installer->getConnection()->createTable($table);
    }
}
