<?php
declare(strict_types=1);
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
 * @category   BSS
 * @package    Bss_InventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\InventoryReport\Block\Adminhtml\Inventory\Report;

use Bss\BrandRepresentative\Helper\Data;
use Bss\InventoryReport\Model\ResourceModel\Report\InventoryReport\Collection;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Reports\Block\Adminhtml\Grid\AbstractGrid;
use Magento\Reports\Block\Adminhtml\Sales\Grid\Column\Renderer\Date;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;
use \Magento\Sales\Model\Order\Item;

/**
 * Class Grid
 * Bss\InventoryReport\Block\Adminhtml\Sales\Report
 */
class Grid extends AbstractGrid
{
    const PRODUCT_NAME_COL_ID = "product_name";

    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnGroupBy = 'period';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var OrderItemCollectionFactory
     */
    protected $orderItemCollectionFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param Data $helper
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        Data $helper,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        OrderItemCollectionFactory $orderItemCollectionFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        parent::__construct($context, $backendHelper, $resourceFactory, $collectionFactory, $reportsData, $data);
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setCountTotals(false);
        $this->_exportPageSize = 10000;
    }

    /**
     * @inheritdoc
     * @codeCoverageIgnore
     */
    public function getResourceCollectionName()
    {
        return Collection::class;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'period',
            [
                'header' => __('Period'),
                'index' => 'period',
                'sortable' => false,
                'period_type' => $this->getPeriodType(),
                'renderer' => Date::class,
                'html_decorators' => ['nobr'],
                'header_css_class' => 'col-period',
                'column_css_class' => 'col-period'
            ]
        );
        $this->addColumn(
            'p_id',
            [
                'header' => __('Product ID'),
                'index' => 'product_id',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-product-name',
                'column_css_class' => 'col-product-name'
            ]
        );
        $this->addColumn(
            static::PRODUCT_NAME_COL_ID,
            [
                'header' => __('Product Name'),
                'index' => static::PRODUCT_NAME_COL_ID,
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product-name',
                'column_css_class' => 'col-product-name'
            ]
        );
        $this->addColumn(
            'product_sku',
            [
                'header' => __('SKU'),
                'index' => 'product_sku',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-product-sku',
                'column_css_class' => 'col-product-sku'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->getStatusOptions(),
                'sortable' => false,
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );
        $this->addColumn(
            'product_brand',
            [
                'header' => __('Brand'),
                'index' => 'brand_id',
                'type' => 'string',
                'sortable' => false,
                'header_css_class' => 'col-brand',
                'column_css_class' => 'col-brand',
                'frame_callback' => [$this, "getBrandName"]
            ]
        );
        $this->addColumn(
            'inventory_qty',
            [
                'header' => __('Inventory Quantity'),
                'index' => 'inventory_qty',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-qty',
                'column_css_class' => 'col-qty'
            ]
        );
        $this->addColumn(
            'max_order_amount',
            [
                'header' => __('Maximum order amount'),
                'index' => 'max_order_amount',
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-max_order_amount',
                'column_css_class' => 'col-max_order_amount'
            ]
        );
        $this->addColumn(
            'stock_status',
            [
                'header' => __('Stock Status'),
                'index' => 'stock_status',
                'type' => 'options',
                'options' => $this->getStockStatusOptions(),
                'sortable' => false,
                'header_css_class' => 'col-max_order_amount',
                'column_css_class' => 'col-max_order_amount'
            ]
        );
        $this->addColumn(
            'ordered_units_30_days',
            [
                'header' => __('Ordered units in the previous 30 days'),
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-30_days',
                'column_css_class' => 'col-30_days',
                'frame_callback' => [$this, "getOrderedUnits"]
            ]
        );
        $this->addColumn(
            'ordered_units_90_days',
            [
                'header' => __('Ordered units in the previous 90 days'),
                'type' => 'number',
                'sortable' => false,
                'header_css_class' => 'col-90_days',
                'column_css_class' => 'col-90_days',
                'frame_callback' => [$this, "getOrderedUnits"]
            ]
        );
        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Get ordered unit of product
     *
     * @param mixed $value
     * @param \Magento\Framework\DataObject $reportItem
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @return int
     */
    public function getOrderedUnits($value, \Magento\Framework\DataObject $reportItem, BlockInterface $column)
    {
        switch ($column->getId()) {
            case "ordered_units_30_days":
                $day = 30;
                break;
            case "ordered_units_90_days":
                $day = 90;
                break;
        }

        if (!isset($day)) {
            return '0';
        }
        $queryDateFormat = "Y-m-d";
        // UTC date
        $to = date($queryDateFormat);
        $from = date($queryDateFormat, strtotime($day . " days ago"));

        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $collection */
        $collection = $this->orderItemCollectionFactory->create();
        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->columns([
                'order_quantity' => 'SUM(qty_ordered)'
            ])
            ->where(
                new \Zend_Db_Expr(
                    sprintf(
                        "`product_id` = %s OR " .
                        "`product_options` LIKE '%%{\"product_type\":\"grouped\",\"product_id\":\"%s\"}}%%'",
                        $reportItem->getProductId(),
                        $reportItem->getProductId()
                    )
                )
            )
            ->where(
                sprintf(
                    '%s >= "%s" AND %s <= "%s"',
                    $collection->getConnection()->getDateFormatSql("created_at", "%Y-%m-%d"),
                    $from,
                    $collection->getConnection()->getDateFormatSql("created_at", "%Y-%m-%d"),
                    $to
                )
            );

        /** @var Item $item */
        foreach ($collection as $item) {
            $qty = $item->getData("order_quantity");

            if (!$qty) {
                return '0';
            }

            return ((int) $qty) . '';
        }

        return '0';
    }

    /**
     * Get product status options
     *
     * @return array
     */
    public function getStatusOptions(): array
    {
        return [
            '0' => __("Inactive"),
            '1' => __("Active")
        ];
    }

    /**
     * Get stock status options
     *
     * @return array
     */
    public function getStockStatusOptions(): array
    {
        return [
            SourceItemInterface::STATUS_IN_STOCK => __('In Stock'),
            SourceItemInterface::STATUS_OUT_OF_STOCK => __('Out of Stock')
        ];
    }

    /**
     * Get brand name for display in grid
     *
     * @param string|null $value
     * @param \Magento\Reports\Model\Item $reportItem
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @return string|null
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function getBrandName($value, $reportItem, $column): ?string
    {
        try {
            if ($reportItem['brand_id']) {
                $brand = $this->categoryRepository->get($reportItem['brand_id']);

                return $brand->getName();
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                __("BSS.ERROR: Loading brand. %1", $e)
            );
        }

        return null;
    }

    /**
     * Add brand filter
     *
     * @param \Magento\Reports\Model\ResourceModel\Report\Collection\AbstractCollection $collection
     * @param \Magento\Framework\DataObject $filterData
     * @return Grid
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        $brands = $filterData->getData('brands');
        if (isset($brands[0])) {
            $brandIds = explode(',', $brands[0]);
            array_walk($brandIds, function (&$id) {
                $id = (int) $id;
            });
            $collection->setBrandFilter($brandIds);
        }

        return parent::_addCustomFilter($collection, $filterData);
    }
}
