<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Block;

use Magento\Catalog\Block\Product\ProductList\Toolbar as BePlugged;
use Magento\Framework\View\Element\Template;

/**
 * Class Toolbar
 * Set default direction for custom sorting
 */
class Toolbar extends Template
{
    /**
     * Make desc direction for most_viewed and newest sorting
     *
     * @param BePlugged $subject
     * @param string $result
     * @return mixed|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentDirection(BePlugged $subject, $result)
    {
        if ($subject->getCurrentOrder() == "most_viewed" ||
            $subject->getCurrentOrder() == "newest"
        ) {
            $dir = "DESC";
        }

        if (isset($dir)) {
            $this->setData('_current_grid_direction', $dir ?? $result);
            $subject->setData('_current_grid_direction', $dir ?? $result);
        }

        return $dir ?? $result;
    }
}
