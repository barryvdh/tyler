<?php
declare(strict_types=1);
namespace Bss\CustomizeAmastyRolePermissions\Helper;

/**
 * Class Data
 * Customize changes of amasty rolepermission module
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends \Amasty\Rolepermissions\Helper\Data
{
    /**
     * Get all parent categories ids
     *
     * @param false|int $categoryId
     * @return array
     */
    public function getParentIds($categoryId = false)
    {
        if (!$categoryId || $this->category && $this->category->getId() == $categoryId) {
            return $this->category->getParentIds();
        } else {
            return $this->getCategory($categoryId)->getParentIds();
        }
    }
}
