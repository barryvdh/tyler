<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Block\Adminhtml\Export;

use Bss\CustomizeCompanyAccount\Model\Export\Customer;
use Bss\CustomizeCompanyAccount\Model\SubUserField;
use \Magento\ImportExport\Model\Export;

/**
 * Class Filter
 * Sub-user filter grids
 */
class Filter extends \Magento\ImportExport\Block\Adminhtml\Export\Filter
{
    /**
     * @var Customer
     */
    protected $customerExport;

    /**
     * Filter constructor.
     *
     * @param Customer $customerExport
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param array $data
     */
    public function __construct(
        Customer $customerExport,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        array $data = []
    ) {
        $this->customerExport = $customerExport;
        parent::__construct($context, $backendHelper, $importExportData, $data);
    }

    /**
     * Add columns for grid
     *
     * @return Filter|void
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'skip',
            [
                'header' => __('Exclude'),
                'type' => 'checkbox',
                'name' => 'skip',
                'field_name' => Export::FILTER_ELEMENT_GROUP
                    . "[sub_user]["
                    . Export::FILTER_ELEMENT_SKIP . "]"
                    . '[]',
                'filter' => false,
                'sortable' => false,
                'index' => 'field',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id data-grid-checkbox-cell',
                'frame_callback' => [$this, 'skipPermanentField']
            ]
        );
        $this->addColumn(
            'field_label',
            [
                'header' => __('Sub-user Field Label'),
                'index' => 'field_label',
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        $this->addColumn(
            'field',
            [
                'header' => __('Sub-user Field'),
                'index' => 'field',
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-code',
                'column_css_class' => 'col-code'
            ]
        );
        $this->addColumn(
            'filter',
            [
                'header' => __('Filter'),
                'sortable' => false,
                'filter' => false,
                'frame_callback' => [$this, 'decorateFilter']
            ]
        );

        if ($this->hasOperation()) {
            $operation = $this->getOperation();
            $skipAttr = $operation->getSkipAttr();
            if ($skipAttr) {
                $this->getColumn('skip')->setData('values', $skipAttr);
            }
            $filter = $operation->getExportFilter();
            if ($filter) {
                $this->getColumn('filter')->setData('values', $filter);
            }
        }
    }

    /**
     * Render html
     *
     * @return string
     */
    protected function _toHtml()
    {
        return "<div><strong>Sub-user fields filter</strong></div>" . parent::_toHtml();
    }

    /**
     * Skip exclude permanent field
     *
     * @param string $cellHtml
     * @param \Magento\Framework\DataObject $row
     * @param \Magento\Framework\DataObject $column
     * @return string
     */
    public function skipPermanentField($cellHtml, $row, \Magento\Framework\DataObject $column)
    {
        if (in_array(
            $row->getData($column->getIndex()),
            $this->customerExport->getMappingToCustomerFields()
        )) {
            return "";
        }

        return $cellHtml;
    }

    /**
     * Get cell html
     *
     * @param mixed $value
     * @param \Magento\Eav\Model\Entity\Attribute $row
     * @param \Magento\Framework\DataObject $column
     * @param bool $isExport
     * @return \Magento\Framework\Phrase|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function decorateFilter($value, $row, \Magento\Framework\DataObject $column, $isExport)
    {
        $value = null;
        $values = $column->getValues();
        if (is_array($values) && isset($values[$row->getField()])) {
            $value = $values[$row->getField()];
        }

        $filterType = $row->getFilterType();
        switch ($filterType) {
            case Export::FILTER_TYPE_SELECT:
                $cell = $this->_getSelectHtmlWithValue($row, $value);
                break;
            case Export::FILTER_TYPE_MULTISELECT:
                $cell = $this->_getMultiSelectHtmlWithValue($row, $value);
                break;
            case Export::FILTER_TYPE_INPUT:
                $cell = $this->_getInputHtmlWithValue($row, $value);
                break;
            case Export::FILTER_TYPE_DATE:
                $cell = $this->_getDateFromToHtmlWithValue($row, $value);
                break;
            case Export::FILTER_TYPE_NUMBER:
                $cell = $this->_getNumberFromToHtmlWithValue($row, $value);
                break;
            default:
                $cell = __('Unknown attribute filter type');
        }
        return $cell;
    }

    /**
     * Get select filter type html
     *
     * @param SubUserField $attribute
     * @param mixed $value
     * @return \Magento\Framework\Phrase|string
     */
    protected function _getSelectHtmlWithValue($attribute, $value)
    {
        $options = $attribute->getFilterOptions();
        if (count($options)) {
            // add empty value option
            $firstOption = reset($options);

            if ('' === $firstOption['value']) {
                $options[key($options)]['label'] = '';
            } else {
                array_unshift($options, ['value' => '', 'label' => __('-- Not Selected --')]);
            }
            $arguments = [
                'name' => $this->getSubUserFilterElementName($attribute->getField()),
                'id' => $this->getFilterElementId($attribute->getField()),
                'class' => 'admin__control-select select select-export-filter',
            ];
            /** @var $selectBlock \Magento\Framework\View\Element\Html\Select */
            $selectBlock = $this->_layout->createBlock(
                \Magento\Framework\View\Element\Html\Select::class,
                '',
                ['data' => $arguments]
            );
            return $selectBlock->setOptions($options)->setValue($value)->getHtml();
        } else {
            return __('We can\'t filter an attribute with no attribute options.');
        }
    }

    /**
     * Get input filter type html
     *
     * @param \Magento\Eav\Model\Entity\Attribute $subUserField
     * @param mixed $value
     * @return string
     */
    protected function _getInputHtmlWithValue($subUserField, $value)
    {
        $html = '<input type="text" name="' . $this->getSubUserFilterElementName(
            $subUserField->getField()
        ) . '" class="admin__control-text input-text input-text-export-filter"';
        if ($value) {
            $html .= ' value="' . $this->escapeHtml($value) . '"';
        }
        return $html . ' />';
    }

    /**
     * Get date from filter type html
     *
     * @param SubUserField $subUserField
     * @param mixed $value
     * @return string
     */
    protected function _getDateFromToHtmlWithValue($subUserField, $value)
    {
        $arguments = [
            'name' => $this->getSubUserFilterElementName($subUserField->getField()) . '[]',
            'id' => $this->getFilterElementId($subUserField->getField()),
            'class' => 'admin__control-text',
            'date_format' => $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
            ),
        ];
        /** @var $selectBlock \Magento\Framework\View\Element\Html\Date */
        $dateBlock = $this->_layout->createBlock(
            \Magento\Framework\View\Element\Html\Date::class,
            '',
            ['data' => $arguments]
        );
        $fromValue = null;
        $toValue = null;
        if (is_array($value) && count($value) == 2) {
            $fromValue = $this->escapeHtml(reset($value));
            $toValue = $this->escapeHtml(next($value));
        }

        return '<strong class="admin__control-support-text">' . __('From') . ':</strong>&nbsp;'
            . $dateBlock->setValue($fromValue)->getHtml()
            . '&nbsp;<strong class="admin__control-support-text">' . __('To') . ':</strong>&nbsp;'
            . $dateBlock->setId($dateBlock->getId() . '_to')->setValue($toValue)->getHtml();
    }

    /**
     * Get sub--user filter element name
     *
     * @param string $field
     * @return string
     */
    public function getSubUserFilterElementName($field)
    {
        return Export::FILTER_ELEMENT_GROUP . "[sub_user][{$field}]";
    }
}
