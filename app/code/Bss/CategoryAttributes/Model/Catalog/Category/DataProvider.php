<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Model\Catalog\Category;

use Bss\CategoryAttributes\Model\Config\Source\CustomAttributes;

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
        $parentFieldMap['content'] = [...$parentFieldMap['content'], ...CustomAttributes::$lists];
        return $parentFieldMap;
    }
}
