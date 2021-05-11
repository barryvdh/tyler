<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Bss\CustomizeCompanyAccount\Model\ResourceModel\Grid\Customer\Collection as CustomizeGridCustomerCollection;

/**
 * Class RegularFilter
 * Add is_sub_user filter for sub-user with customer in grid
 */
class RegularFilter
{
    /**
     * Add is_sub_user filter for sub-user with customer in grid
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter $subject
     * @param callable $proceed
     * @param Collection $collection
     * @param Filter $filter
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundApply(
        \Magento\Framework\View\Element\UiComponent\DataProvider\RegularFilter $subject,
        callable $proceed,
        Collection $collection,
        Filter $filter
    ) {
        if (!$collection instanceof CustomizeGridCustomerCollection) {
            return $proceed($collection, $filter);
        }

        switch ($filter->getField()) {
            case "is_sub_user":
                if ($filter->getValue()) {
                    $collection->addFieldToFilter(
                        'entity_id',
                        ['null' => true]
                    );
                } else {
                    $collection->addFieldToFilter(
                        'entity_id',
                        ['neq' => "NULL"]
                    );
                }
                break;
            case "name":
                $this->processSubUserField($collection, $filter->getField(), "sub_name", $filter);
                break;
            case "email":
                $this->processSubUserField($collection, $filter->getField(), "sub_email", $filter);
                break;
            case "entity_id":
                $subUserIds = [];
                if (is_array($filter->getValue())) {
                    $subUserPattern = "/(sub-([0-9]+))/";
                    foreach ($filter->getValue() as $value) {
                        if (preg_match($subUserPattern, $value)) {
                            $subUserIds[] = preg_replace($subUserPattern, '${2}', $value);
                        }
                    }

                    if (!empty($subUserIds)) {
                        $subUserFilter = clone $filter;
                        $subUserFilter->setValue($subUserIds);
                        $this->processSubUserField($collection, $filter->getField(), "sub_id", $filter, $subUserFilter);
                    } else {
                        $proceed($collection, $filter);
                    }
                } else {
                    $proceed($collection, $filter);
                }
                break;
            default:
                $proceed($collection, $filter);
        }
    }

    /**
     * Add sub user field filter
     *
     * @param Collection $collection
     * @param string $customerField - customer field
     * @param string $field - sub user field
     * @param Filter $filter - customer filter
     * @param Filter|null $subUserFilter - sub-user-filter
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function processSubUserField($collection, $customerField, $field, $filter, $subUserFilter = null)
    {
        if ($subUserFilter === null) {
            $subUserFilter = $filter;
        }
        $collection->addFieldToFilter(
            ["main_table." . $customerField, "sub_user." . $field],
            [
                [$filter->getConditionType() => $filter->getValue()],
                [$filter->getConditionType() => $subUserFilter->getValue()]
            ]

        );
    }
}
