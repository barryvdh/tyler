<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Model\Brand\BrandList;

/**
 * Class Toolbar
 */
class Toolbar extends \Magento\Catalog\Model\Product\ProductList\Toolbar
{
    /**
     * GET parameter page variable name
     */
    const PAGE_PARM_NAME = 'p';

    /**
     * Sort order cookie name
     */
    const ORDER_PARAM_NAME = 'brand_list_order';

    /**
     * Sort direction cookie name
     */
    const DIRECTION_PARAM_NAME = 'brand_list_dir';

    /**
     * Sort mode cookie name
     */
    const MODE_PARAM_NAME = 'brand_list_mode';

    /**
     * Products per page limit order cookie name
     */
    const LIMIT_PARAM_NAME = 'brand_list_limit';
}
