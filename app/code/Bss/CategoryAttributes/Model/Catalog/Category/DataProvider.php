<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Model\Catalog\Category;

/**
 * Class DataProvider
 * Add scope to custom attribute
 */
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
    /**
     * Fix scope label
     *
     * @return array
     */
    protected function getFieldsMap()
    {
        $parentFieldMap = parent::getFieldsMap();

        array_push($parentFieldMap['content'], 'cover_category');
        array_push($parentFieldMap['content'], 'contact_us_embedded');
        array_push($parentFieldMap['content'], 'schedule_visit_embedded');
        return $parentFieldMap;
    }
}
