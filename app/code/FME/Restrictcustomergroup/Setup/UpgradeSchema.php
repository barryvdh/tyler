<?php

namespace FME\Restrictcustomergroup\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
		$installer = $setup;
		$installer->startSetup();
		$setup->startSetup();

		if (version_compare($context->getVersion(), '1.2.2', '<')) {

			$setup->getConnection()->addColumn(
				$setup->getTable('fme_restrictcustomergroup_rule'),
				'start_date',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					['nullable' => true, 'default' => null],
					'comment' => 'Start Date',
				]);

			$setup->getConnection()->addColumn(
				$setup->getTable('fme_restrictcustomergroup_rule'),
				'end_date',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					['nullable' => true, 'default' => null],
					'comment' => 'End Date',
				]);

			// $setup->getConnection()->changeColumn(
			// 	$setup->getTable('fme_restrictcustomergroup_rule'),
			// 	'end_date',
			// 	'end_date',
			// 	[
			// 		'type' => Table::TYPE_TEXT,
			// 		'nullable' => true,
			// 		'default' => '',
			// 		'comment' => 'End Date',
			// 	]
			// );
		}

		$setup->endSetup();
	}
}
