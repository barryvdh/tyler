<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Model\Export;

use Magento\ImportExport\Model\Export;
use Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory as SubUserCollectionFactory;

/**
 * Class Customer
 * Override for sub-user information
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends \Magento\CustomerImportExport\Model\Export\Customer
{
    protected $validSubUserFields = [];

    /**
     * @var SubUserCollectionFactory
     */
    protected $subUserCollectionFactory;

    /**
     * @var \Bss\CustomizeCompanyAccount\Model\InitSubUserFieldCollection
     */
    protected $initSubUserFieldCollection;

    /**
     * Customer constructor.
     *
     * @param SubUserCollectionFactory $subUserCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory
     * @param \Bss\CustomizeCompanyAccount\Model\InitSubUserFieldCollection $initSubUserFieldCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        SubUserCollectionFactory $subUserCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerColFactory,
        \Bss\CustomizeCompanyAccount\Model\InitSubUserFieldCollection $initSubUserFieldCollection,
        array $data = []
    ) {
        $this->subUserCollectionFactory = $subUserCollectionFactory;
        $this->initSubUserFieldCollection = $initSubUserFieldCollection;
        parent::__construct(
            $scopeConfig,
            $storeManager,
            $collectionFactory,
            $resourceColFactory,
            $localeDate,
            $eavConfig,
            $customerColFactory,
            $data
        );
    }

    /**
     * Add sub-user header columns
     *
     * @return array|string[]
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _getHeaderColumns()
    {
        $this->getSubUserHeaderColumns();
        return array_merge(parent::_getHeaderColumns(), $this->validSubUserFields);
    }

    /**
     * Add Sub-user fields to header
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function getSubUserHeaderColumns()
    {
        $exportFilter = $this->_parameters[Export::FILTER_ELEMENT_GROUP] ?? [];
        if (isset($exportFilter['sub_user']) && empty($this->validSubUserFields)) {
            $skippedAttributes = [];

            if (isset($exportFilter['sub_user'][Export::FILTER_ELEMENT_SKIP]) &&
                !empty($exportFilter['sub_user'][Export::FILTER_ELEMENT_SKIP]) &&
                is_array($exportFilter['sub_user'][Export::FILTER_ELEMENT_SKIP])
            ) {
                $skippedAttributes = array_flip(
                    $exportFilter['sub_user'][Export::FILTER_ELEMENT_SKIP]
                );
            }
            $attributeCodes = [];
            foreach ($exportFilter['sub_user'] as $field => $value) {
                if (!isset($skippedAttributes[$field]) && $field !== Export::FILTER_ELEMENT_SKIP) {
                    $attributeCodes[] = $field;
                }
            }
            $this->validSubUserFields = $attributeCodes;
        }
    }

    /**
     * Iterate through given collection page by page and export items
     *
     * @param \Magento\Framework\Data\Collection\AbstractDb $collection
     * @throws \Exception
     */
    protected function _exportCollectionByPages(\Magento\Framework\Data\Collection\AbstractDb $collection)
    {
        parent::_exportCollectionByPages($collection);

        if (isset($this->_parameters[Export::FILTER_ELEMENT_GROUP]['sub_user']) &&
            is_array($this->_parameters[Export::FILTER_ELEMENT_GROUP]['sub_user'])
        ) {
            $subUserCollection = $this->prepareSubUserCollection();
            $this->_byPagesIterator->iterate($subUserCollection, $this->_pageSize, [[$this, 'exportSubUserItem']]);
        }
    }

    /**
     * Export sub-user data
     *
     * @param \Bss\CompanyAccount\Model\SubUser $item
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportSubUserItem($item)
    {
        $row = $this->addEmptyCustomerDataToRow();
        $this->addSubUserInfoDataToRow($row, $item);
        $this->getWriter()->writeRow($row);
    }

    /**
     * Add subuser info data to row
     *
     * @param array $row
     * @param \Bss\CompanyAccount\Model\SubUser $item
     */
    protected function addSubUserInfoDataToRow(&$row, $item)
    {
        foreach ($this->validSubUserFields as $field) {
            $row[$field] = $item->getData($field);
        }
    }

    /**
     * Add empty value to customer info col
     *
     * @return array
     */
    protected function addEmptyCustomerDataToRow()
    {
        $validAttributeCodes = $this->_getExportAttributeCodes();
        $row = [];
        foreach ($validAttributeCodes as $attributeCode) {
            $row[$attributeCode] = "";
        }

        return $row;
    }

    /**
     * Prepare subuser collection, include header columns, filters
     *
     * @return \Bss\CompanyAccount\Model\ResourceModel\SubUser\Collection
     * @throws \Exception
     * @SuppressWarnings(CyclomaticComplexity)
     */
    // @codingStandardsIgnoreStart
    protected function prepareSubUserCollection()
    {
        /** @var \Bss\CompanyAccount\Model\ResourceModel\SubUser\Collection $collection */
        $collection = $this->subUserCollectionFactory->create();

        $fieldCollection = $this->initSubUserFieldCollection->execute();
        $subUserFilter = $this->_parameters[Export::FILTER_ELEMENT_GROUP]['sub_user'];

        if (empty($this->validSubUserFields)) {
            $this->getSubUserHeaderColumns();
        }

        $collection->addFieldToSelect($this->validSubUserFields);
        /** @var \Bss\CustomizeCompanyAccount\Model\SubUserField $field */
        foreach ($fieldCollection as $field) {
            if (isset($subUserFilter[$field->getField()])) {
                switch ($field->getFilterType()) {
                    case Export::FILTER_TYPE_SELECT:
                        if (is_scalar($subUserFilter[$field->getField()])) {
                            if (trim($subUserFilter[$field->getField()]) ||
                                trim($subUserFilter[$field->getField()]) == "0"
                            ) {
                                $collection->addFieldToFilter(
                                    $field->getField(),
                                    ['eq' => $subUserFilter[$field->getField()]]
                                );
                            }
                        }
                        break;
                    case Export::FILTER_TYPE_DATE:
                        if (is_array($subUserFilter[$field->getField()]) &&
                            count($subUserFilter[$field->getField()]) == 2
                        ) {
                            $from = array_shift($subUserFilter[$field->getField()]);
                            $to = array_shift($subUserFilter[$field->getField()]);

                            if (is_scalar($from) && !empty($from)) {
                                $date = (new \DateTime($from))->format('m/d/Y');
                                $collection->addFieldToFilter($field->getField(), ['from' => $date, 'date' => true]);
                            }
                            if (is_scalar($to) && !empty($to)) {
                                $date = (new \DateTime($to))->format('m/d/Y');
                                $collection->addFieldToFilter($field->getField(), ['to' => $date, 'date' => true]);
                            }
                        }
                        break;
                    default:
                        if (is_scalar($subUserFilter[$field->getField()]) && trim($subUserFilter[$field->getField()])) {
                            $collection->addFieldToFilter(
                                $field->getField(),
                                ['like' => "%{$subUserFilter[$field->getField()]}%"]
                            );
                        }
                        break;
                }
            }
        }

        return $collection;
    }
    // @codingStandardsIgnoreEnd
}
