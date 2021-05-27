<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Block\Brand\Pages\BrandList;

/**
 * Class ToolBar - fix cứng limit, order for brand grid
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    const DEFAULT_ORDER = "name";
    const DEFAULT_LIMIT = 15;
    const DEFAULT_DIRECTION = "acs";

    /**
     * @var string
     */
    protected $_template = "Bss_BrandRepresentative::brand/list/toolbar.phtml";

    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        return $this;
    }

    /**
     * Get sort order array
     *
     * @return array
     */
    public function getAvailableOrders()
    {
        return [
            "name" => __("Brand Name"),
            "most_viewed" => __("Most Viewed"),
            "created_at" => __("Newest")
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLimit()
    {
        return self::DEFAULT_LIMIT;
    }
}
