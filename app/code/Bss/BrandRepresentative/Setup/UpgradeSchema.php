<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Setup;

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
            $connection = $setup->getConnection();
            $tbName = $setup->getTable('bss_brandsales_report');
            $connection->addColumn(
                $tbName,
                'brand_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'unsigned' => true,
                    'length' => null,
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
                'company_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Company Name'
                ]
            );
            $connection->addColumn(
                $tbName,
                'product_options',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => null,
                    'comment' => 'Children of product'
                ]
            );
            if ($setup->getConnection()->tableColumnExists($tbName, 'brand')) {
                $connection->changeColumn(
                    $tbName,
                    'brand',
                    'category_name',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => null,
                        'comment' => 'In Categories'
                    ]
                );
            }
        }
    }
}
