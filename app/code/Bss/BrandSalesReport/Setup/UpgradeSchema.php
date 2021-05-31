<?php
declare(strict_types=1);
namespace Bss\BrandSalesReport\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * Modify brandsales report
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritDoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->modifyTable($setup, "brandsalesreport_aggregated_daily");
            $this->modifyTable($setup, "brandsalesreport_aggregated_monthly");
            $this->modifyTable($setup, "brandsalesreport_aggregated_yearly");
        }
    }

    /**
     * Modify table, add brand col
     *
     * @param SchemaSetupInterface $setup
     * @param string $tbName
     */
    protected function modifyTable(SchemaSetupInterface $setup, string $tbName)
    {
        $connection = $setup->getConnection();
        $tbName = $setup->getTable($tbName);
        $connection->addColumn(
            $tbName,
            'brand_id',
            [
                'type' => Table::TYPE_INTEGER,
                'unsigned' => true,
                'length' => null,
                'nullable' => true,
                'comment' => 'Brand ID'
            ]
        );
        $connection->addColumn(
            $tbName,
            'brand_name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null,
                'comment' => 'Brand Name'
            ]
        );
        $connection->addColumn(
            $tbName,
            'product_type',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Product Type',
                'after' => 'product_name'
            ]
        );

        $connection->addColumn(
            $tbName,
            'company_name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Company Name'
            ]
        );
        $connection->addColumn(
            $tbName,
            'address',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Address'
            ]
        );
        $connection->addColumn(
            $tbName,
            'city',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'City'
            ]
        );
        $connection->addColumn(
            $tbName,
            'province',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Province'
            ]
        );
        $connection->addColumn(
            $tbName,
            'product_options',
            [
                'type' => Table::TYPE_TEXT,
                'length' => null,
                'comment' => 'Product options'
            ]
        );

        if ($setup->getConnection()->tableColumnExists($tbName, "product_brand")) {
            $connection->changeColumn(
                $tbName,
                'product_brand',
                'category_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'comment' => 'In brand categories'
                ]
            );
        }
    }
}
