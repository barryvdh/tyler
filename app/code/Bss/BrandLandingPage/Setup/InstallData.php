<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Setup\CustomerSetupFactory;

/**
 * Create signup sources customer attribute
 */
class InstallData implements InstallDataInterface
{
    /**
     * Customer setup factory
     *
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * InstallData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * @inheritDoc
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'signup_sources',
            [
                'label' => 'Signup Sources',
                'input' => 'select',
                'required' => false,
                'sort_order' => 150,
                'visible' => true,
                'user_defined' => true,
                'position' => 1000,
                'system' => false,
                'is_used_in_grid' => true,
                'type' => 'int',
                'source' => \Bss\BrandLandingPage\Model\Customer\Attribute\Source\Brand::class
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'signup_sources')
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer', 'b2b_account_create'],
            ]);

        $attribute->save();
        $setup->endSetup();
    }
}
