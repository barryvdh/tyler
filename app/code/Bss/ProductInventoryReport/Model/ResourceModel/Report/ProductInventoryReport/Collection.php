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
 * @package    Bss_ProductInventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\ProductInventoryReport\Model\ResourceModel\Report\ProductInventoryReport;

use Bss\ProductInventoryReport\Setup\InstallSchema;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Report;
use Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Zend_Db_Select_Exception;

/**
 * Class Collection
 * Bss\ProductInventoryReport\Model\ResourceModel\Report\ProductInventoryReport
 */
class Collection extends AbstractCollection
{
    protected $brands;

    /**
     * Selected columns
     *
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * Tables per period
     *
     * @var array
     */
    protected $tableForPeriod = [
        'daily'   => InstallSchema::TBL_INVENTORY_REPORT_DAILY,
        'monthly' => InstallSchema::TBL_INVENTORY_REPORT_MONTHLY,
        'yearly'  => InstallSchema::TBL_INVENTORY_REPORT_YEARLY,
    ];

    /**
     * @param EntityFactory $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param Report $resource
     * @param AdapterInterface|null $connection
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Report $resource,
        AdapterInterface $connection = null
    ) {
        $resource->init($this->getTableByAggregationPeriod('daily'));
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * Set brand filter
     *
     * @param array $brands
     * @return $this
     */
    public function setBrandFilter($brands)
    {
        $this->brands = $brands;
        return $this;
    }

    /**
     * Return ordered filed
     *
     * @return string
     */
    protected function getOrderedField()
    {
        return 'qty_ordered';
    }

    /**
     * Return table per period
     *
     * @param string $period
     * @return mixed
     */
    public function getTableByAggregationPeriod($period)
    {
        return $this->tableForPeriod[$period];
    }

    /**
     * Retrieve selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns(): array
    {
        $connection = $this->getConnection();

        if (!$this->_selectedColumns) {
            $this->_selectedColumns = [
                'period' => sprintf(
                    'MAX(%s)',
                    $connection->getDateFormatSql('period', '%Y-%m-%d')
                ),
                'product_id'            => 'product_id',
                'product_sku'           => 'MAX(product_sku)',
                'product_name'          => 'MAX(product_name)',
                'stock_status'          => 'MAX(stock_status)',
                'status'                => 'MAX(status)',
                'brand_id'              => 'MAX(brand_id)',
                'max_order_amount'      => 'MAX(max_order_amount)',
                'inventory_qty'         => 'MAX(inventory_qty)',
            ];
            if ('year' == $this->_period) {
                $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y');
            } elseif ('month' == $this->_period) {
                $this->_selectedColumns['period'] = $connection->getDateFormatSql('period', '%Y-%m');
            }
        }

        return $this->_selectedColumns;
    }

    /**
     * Make select object for date boundary
     *
     * @param string $from
     * @param string $to
     * @return Select
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _makeBoundarySelect($from, $to)
    {
        $connection = $this->getConnection();
        $cols = $this->_getSelectedColumns();
        $select = $connection->select()->from(
            $this->getResource()->getMainTable(),
            $cols
        )->where(
            'period >= ?',
            $from
        )->where(
            'period <= ?',
            $to
        )->group(
            ['product_id']
        )->order(
            'product_id DESC'
        );

        $this->_applyStoresFilterToSelect($select);
        $this->applyBrandFilter($select);

        return $select;
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _applyAggregatedTable()
    {
        $select = $this->getSelect();

        //if grouping by product, not by period
        if (!$this->_period) {
            $cols = $this->_getSelectedColumns();
            if ($this->_from || $this->_to) {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
                $select->from($mainTable, $cols);
            } else {
                $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
                $select->from($mainTable, $cols);
            }

            return $this;
        }

        if ('year' === $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('yearly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } elseif ('month' === $this->_period) {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('monthly'));
            $select->from($mainTable, $this->_getSelectedColumns());
        } else {
            $mainTable = $this->getTable($this->getTableByAggregationPeriod('daily'));
            $select->from($mainTable, $this->_getSelectedColumns());
        }
        $select->group(['period', 'product_id']);

        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(Select::ORDER);
        return $this->getConnection()->select()->from($select, 'COUNT(*)');
    }

    /**
     * Escape apply store filter
     *
     * @return $this
     */
    protected function _applyStoresFilter()
    {
        return $this;
    }

    /**
     * Query selected brands
     *
     * @param \Magento\Framework\DB\Select|null $select
     * @return $this
     */
    protected function applyBrandFilter(\Magento\Framework\DB\Select $select = null)
    {
        if (empty($this->brands) || !is_array($this->brands)) {
            return $this;
        }

        if (!$select) {
            $select = $this->getSelect();
        }
        $select->where('brand_id IN (?)', $this->brands);

        return $this;
    }

    /**
     * Redeclare parent method for applying filters after parent method
     *
     * But before adding unions
     *
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws Zend_Db_Select_Exception|\Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        if ($this->_period) {
            $selectUnions = [];

            // apply date boundaries (before calling $this->_applyDateRangeFilter())
            $periodFrom = ($this->_from !== null) ? new \DateTime($this->_from) : null;
            $periodTo = ($this->_to !== null) ? new \DateTime($this->_to) : null;
            if ('year' == $this->_period) {
                if ($periodFrom) {
                    // not the first day of the year
                    if ($periodFrom->format('m') != 1 || $periodFrom->format('d') != 1) {
                        $dtFrom = clone $periodFrom;
                        // last day of the year
                        $dtTo = clone $periodFrom;
                        $dtTo->setDate($dtTo->format('Y'), 12, 31);
                        if (!$periodTo || $dtTo < $periodTo) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // first day of the next year
                            $this->_from = clone $periodFrom;
                            $this->_from->modify('+1 year');
                            $this->_from->setDate($this->_from->format('Y'), 1, 1);
                            $this->_from = $this->_from->format('Y-m-d');
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the year
                    if ($periodTo->format('m') != 12 || $periodTo->format('d') != 31) {
                        $dtFrom = clone $periodTo;
                        $dtFrom->setDate($dtFrom->format('Y'), 1, 1);
                        // first day of the year
                        $dtTo = clone $periodTo;
                        if (!$periodFrom || $dtFrom > $periodFrom) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // last day of the previous year
                            $this->_to = clone $periodTo;
                            $this->_to->modify('-1 year');
                            $this->_to->setDate($this->_to->format('Y'), 12, 31);
                            $this->_to = $this->_to->format('Y-m-d');
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same year
                    if ($periodTo->format('Y') == $periodFrom->format('Y')) {
                        $dtFrom = clone $periodFrom;
                        $dtTo = clone $periodTo;
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->format('Y-m-d'),
                            $dtTo->format('Y-m-d')
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }
            } elseif ('month' == $this->_period) {
                if ($periodFrom) {
                    // not the first day of the month
                    if ($periodFrom->format('d') != 1) {
                        $dtFrom = clone $periodFrom;
                        // last day of the month
                        $dtTo = clone $periodFrom;
                        $dtTo->modify('+1 month');
                        $dtTo->setDate($dtTo->format('Y'), $dtTo->format('m'), 1);
                        $dtTo->modify('-1 day');
                        if (!$periodTo || $dtTo < $periodTo) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // first day of the next month
                            $this->_from = clone $periodFrom;
                            $this->_from->modify('+1 month');
                            $this->_from->setDate($this->_from->format('Y'), $this->_from->format('m'), 1);
                            $this->_from = $this->_from->format('Y-m-d');
                        }
                    }
                }

                if ($periodTo) {
                    // not the last day of the month
                    if ($periodTo->format('d') != $periodTo->format('t')) {
                        $dtFrom = clone $periodTo;
                        $dtFrom->setDate($dtFrom->format('Y'), $dtFrom->format('m'), 1);
                        // first day of the month
                        $dtTo = clone $periodTo;
                        if (!$periodFrom || $dtFrom > $periodFrom) {
                            $selectUnions[] = $this->_makeBoundarySelect(
                                $dtFrom->format('Y-m-d'),
                                $dtTo->format('Y-m-d')
                            );

                            // last day of the previous month
                            $this->_to = clone $periodTo;
                            $this->_to->setDate($this->_to->format('Y'), $this->_to->format('m'), 1);
                            $this->_to->modify('-1 day');
                            $this->_to = $this->_to->format('Y-m-d');
                        }
                    }
                }

                if ($periodFrom && $periodTo) {
                    // the same month
                    if ($periodTo->format('Y') == $periodFrom->format('Y') &&
                        $periodTo->format('m') == $periodFrom->format('m')
                    ) {
                        $dtFrom = clone $periodFrom;
                        $dtTo = clone $periodTo;
                        $selectUnions[] = $this->_makeBoundarySelect(
                            $dtFrom->format('Y-m-d'),
                            $dtTo->format('Y-m-d')
                        );

                        $this->getSelect()->where('1<>1');
                    }
                }
            }

            $this->_applyDateRangeFilter();

            // add unions to select
            if ($selectUnions) {
                $unionParts = [];
                $cloneSelect = clone $this->getSelect();
                $unionParts[] = '(' . $cloneSelect . ')';
                foreach ($selectUnions as $union) {
                    $unionParts[] = '(' . $union . ')';
                }
                $this->getSelect()->reset()->union($unionParts, Select::SQL_UNION_ALL);
            }

            $this->getSelect()->order(['period ASC', 'product_id DESC']);
        }

        return $this;
    }

    /**
     * Apply brand fields
     *
     * @return Collection
     */
    protected function _applyCustomFilter()
    {
        $this->applyBrandFilter();
        return parent::_applyCustomFilter();
    }
}
