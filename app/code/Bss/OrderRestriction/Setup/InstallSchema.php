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
                OrderRule::CUSTOMER_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                "Related Customer"
            )->addColumn(
                OrderRule::QTY_PER_ORDER,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                "The qty/order"
            )->addColumn(
                OrderRule::ORDERS_PER_MONTH,
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'unsigned' => true
                ],
                "Number of order/month"
            )->addForeignKey(
                $installer->getFkName(
                    OrderRuleResource::TABLE,
                    OrderRule::CUSTOMER_ID,
                    'customer_entity',
                    'entity_id'
                ),
                OrderRule::CUSTOMER_ID,
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->setComment("The Order Restriction Table");

        $installer->getConnection()->createTable($table);
    }
}
