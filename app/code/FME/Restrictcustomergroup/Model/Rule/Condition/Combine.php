<?php
/**
 * Class for Restrictcustomergroup Combine
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{

    /**
     * @var \FME\Restrictcustomergroup\Model\Rule\Condition\ProductFactory
     */
    protected $_productFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \FME\Restrictcustomergroup\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \FME\Restrictcustomergroup\Model\Rule\Condition\ProductFactory $conditionFactory,
        array $data = []
    ) {

        $this->_productFactory = $conditionFactory;
        parent::__construct($context, $data);
        $this->setType('FME\Restrictcustomergroup\Model\Rule\Condition\Combine');
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'FME\Restrictcustomergroup\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
              [
                  'value' => 'FME\Restrictcustomergroup\Model\Rule\Condition\Combine',
                  'label' => __('Conditions Combination'),
              ],
              [
                'label' => __('Product Attribute'), 'value' => $attributes
              ]
            ]
        );
        return $conditions;
    }

    /**
     * @param array $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            /** @var Product|Combine $condition */
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
