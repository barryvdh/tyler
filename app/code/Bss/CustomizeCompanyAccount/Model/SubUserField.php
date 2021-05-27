<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Model;

use Magento\Framework\DataObject;

/**
 * Class SubUserField
 * Subuser field model
 */
class SubUserField extends DataObject
{
    /**
     * Get subUser field
     *
     * @return string
     */
    public function getField()
    {
        return $this->getData("field");
    }

    /**
     * Set subUser field
     *
     * @param string $field
     * @return SubUserField
     */
    public function setField($field)
    {
        return $this->setData("field", $field);
    }

    /**
     * Get field label
     *
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->getData('field_label');
    }

    /**
     * Set field label
     *
     * @param string $label
     * @return SubUserField
     */
    public function setFieldLabel($label)
    {
        return $this->setData('field_label', $label);
    }

    /**
     * Get filter type
     *
     * @return string
     */
    public function getFilterType()
    {
        return $this->getData('filter_type');
    }

    /**
     * Set filter type
     *
     * @param string $type
     * @return SubUserField
     */
    public function setFilterType(string $type = \Magento\ImportExport\Model\Export::FILTER_TYPE_INPUT)
    {
        return $this->setData('filter_type', $type);
    }

    /**
     * Get filter options
     *
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->getData('options');
    }

    /**
     * Set filter options
     *
     * @param array $options
     * @return SubUserField
     */
    public function setFilterOptions($options)
    {
        return $this->setData('options', $options);
    }
}
