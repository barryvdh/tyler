<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Block;

use Magento\Catalog\Block\Product\ProductList\Toolbar as BePlugged;
use Magento\Framework\View\Element\Template;

class Toolbar extends Template
{
    /**
     * Description
     *
     * @param BePlugged $subject
     * @param string $result
     * @return mixed|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentDirection(BePlugged $subject, $result)
    {
        switch ($subject->getCurrentOrder()) {
            case "most_viewed":
            case "created_at":
                $dir = "DESC";
                break;
        }
        if (isset($dir)) {
            $this->setData('_current_grid_direction', $dir ?? $result);
            $subject->setData('_current_grid_direction', $dir ?? $result);
        }

        return $dir ?? $result;
    }
}
