<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Component\Filters\Type;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Ui\Component\Filters\FilterModifier;

use Magento\Framework\App\ResourceConnection;
use Bss\AdminProductsGridwCategory\Model\ResourceModel\Product\Filter;
use Bss\AdminProductsGridwCategory\Helper\Data;

abstract class AbstractFilter extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'filter';

    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filters';

    /**
     * Filter data
     *
     * @var array
     */
    protected $filterData;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterModifier
     */
    protected $filterModifier;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Filter
     */
    protected $customFilter;

    /**
     * AbstractFilter constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param ResourceConnection $resource
     * @param Data $helper
     * @param Filter $customFilter
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        ResourceConnection $resource,
        Data $helper,
        Filter $customFilter,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;

        $this->filterModifier = $filterModifier;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->customFilter = $customFilter;
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $this->filterModifier->applyFilterModifier($this->getContext()->getDataProvider(), $this->getName());
        parent::prepare();
    }
}
