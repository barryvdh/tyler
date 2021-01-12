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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Setup;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Customer setup factory
     *
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * EAV attribute
     *
     * @var eavAttribute
     */
    private $eavAttribute;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param IndexerRegistry $indexerRegistry
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        IndexerRegistry $indexerRegistry,
        AttributeSetFactory $attributeSetFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            $customerSetup->updateAttribute(
                Customer::ENTITY,
                'b2b_activasion_status',
                'source_model',
                \Bss\B2bRegistration\Model\Config\Source\CustomerAttribute::class
            );
            $customerSetup->updateAttribute(
                Customer::ENTITY,
                'b2b_activasion_status',
                'frontend_input',
                'select'
            );
        }

        $indexer = $this->indexerRegistry->get(Customer::CUSTOMER_GRID_INDEXER_ID);
        $indexer->reindexAll();
        $this->eavConfig->clear();
        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

            $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $customerSetup->addAttribute(Customer::ENTITY, 'b2b_normal_customer_group', [
                'label' => 'Normal Customer Group',
                'input' => 'text',
                'required' => false,
                'sort_order' => 120,
                'visible' => false,
                'user_defined' => true,
                'position' => 1000,
                'system' => false,
                'is_used_in_grid' => false,
                'frontend_input' => 'text',
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'b2b_normal_customer_group')
                ->addData([
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => [],
                ]);

            $attribute->save();
        }
        if (version_compare($context->getVersion(), '1.2.5', '<')) {
            $this->processCustomerForm($setup);
        }
        $setup->endSetup();
    }

    /**
     * Insert new attribute to b2b_account_create form
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     */
    private function processCustomerForm($setup)
    {
        $data = [];
        $listAttribute = ['prefix', 'firstname', 'middlename', 'lastname', 'suffix', 'email', 'dob', 'taxvat', 'created_at', 'gender'];
        foreach ($listAttribute as $attribute) {
            $data[] = [
                'form_code' => 'b2b_account_create',
                'attribute_id' => $this->eavAttribute->getIdByCode('customer', $attribute)
            ];
        }
        $setup->getConnection()->insertMultiple($setup->getTable('customer_form_attribute'), $data);
    }
}
