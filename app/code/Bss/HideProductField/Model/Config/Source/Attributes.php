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
 * @package    Bss_HideProductField
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HideProductField\Model\Config\Source;

/**
 * @api
 * @since 100.0.2
 */
class Attributes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * Attribute Factory
     * @var object
     */
    protected $attributeFactory;

    /**
     * Attributes constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Product attributes
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $entityId = $this->eavConfig
            ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)->getEntityTypeId();
        $attributeInfo = $this->attributeFactory->create()->addFieldToFilter('entity_type_id', $entityId);
        foreach ($attributeInfo as $attributeValue) {
            $attributeCode = $attributeValue->getAttributeCode();
            $attributeLabel = $attributeValue->getFrontendLabel();
            $options[] = [
                'label' => $attributeLabel,
                'value' => $attributeCode,
            ];
        }
        return  $options;
    }
}
