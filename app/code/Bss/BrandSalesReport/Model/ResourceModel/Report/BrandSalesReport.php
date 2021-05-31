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
 * @category   BSS
 * @package    Bss_BrandSalesReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandSalesReport\Model\ResourceModel\Report;

use Bss\BrandRepresentative\Model\ResourceModel\SalesReport\CollectionFactory;
use Bss\BrandSalesReport\Model\Flag;
use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone\Validator;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;
use Magento\Sales\Model\ResourceModel\Report\AbstractReport;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class BrandSalesReport
 * Bss\BrandSalesReport\Model\ResourceModel\BrandSalesReport
 */
class BrandSalesReport extends AbstractReport
{
    public const AGGREGATION_DAILY = 'brandsalesreport_aggregated_daily';
    public const AGGREGATION_MONTHLY = 'brandsalesreport_aggregated_monthly';
    public const AGGREGATION_YEARLY = 'brandsalesreport_aggregated_yearly';

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CollectionFactory
     */
    protected $bssReportCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     * @param Validator $timezoneValidator
     * @param DateTime $dateTime
     * @param ResourceConnection $resource
     * @param TimezoneInterface $timezone
     * @param CollectionFactory $bssReportCollectionFactory
     * @param Json $json
     * @param string|null $connectionName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory,
        Validator $timezoneValidator,
        DateTime $dateTime,
        ResourceConnection $resource,
        TimezoneInterface $timezone,
        CollectionFactory $bssReportCollectionFactory,
        Json $json,
        $connectionName = null
    ) {
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $connectionName
        );
        $this->resource = $resource;
        $this->timezone = $timezone;
        $this->logger = $logger;
        $this->bssReportCollectionFactory = $bssReportCollectionFactory;
        $this->json = $json;
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::AGGREGATION_DAILY, 'id');
    }

    /**
     * Aggregate Orders data by order created at
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return $this
     * @throws Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aggregate($from = null, $to = null)
    {
        $mainTable = $this->getMainTable();
        $connection = $this->getConnection();
        //$this->getConnection()->beginTransaction();

        try {
            $this->truncateTable();

            $insertBatches = [];

            /**
             * replace below collection with your custom collection
             * you have to insert data to your aggregate report table
             */
            //DEMO Collection
            /*
            $collection = [
                [
                    'period'       => date('Y-m-d'),
                    'order_id'     => 1,
                    'store_id'     => 1,
                    'product_id'   => 1,
                    'product_sku'  => 'SKU01',
                    'product_name' => 'Joust Double Bag',
                    'product_brand' => 'Rel Decal',
                    'product_brand_email' => 'isuka7112@gmail.com',
                    'qty_ordered'  => 5
                ],
                [
                    'period'       => date('Y-m-d', strtotime("+1 days")),
                    'order_id'     => 2,
                    'store_id'     => 1,
                    'product_id'   => 1,
                    'product_sku'  => 'SKU01',
                    'product_name' => 'Joust Double Bag',
                    'product_brand' => 'Rel Decal',
                    'product_brand_email' => 'isuka7112@gmail.com',
                    'qty_ordered'  => 8
                ],
                [
                    'period'       => date('Y-m-d', strtotime("+1 months")),
                    'order_id'     => 3,
                    'store_id'     => 1,
                    'product_id'   => 1,
                    'product_sku'  => 'SKU01',
                    'product_name' => 'Joust Double Bag',
                    'product_brand' => 'Rel Decal',
                    'product_brand_email' => 'isuka7112@gmail.com',
                    'qty_ordered'  => 2
                ]
            ];
            */
            //END DEMO COLLECTION

            $collection = $this->bssReportCollectionFactory->create()->getData();
            //Convert Collection to correct insert batches
            if ($collection) {
                foreach ($collection as $info) {
                    $insertBatches[] = [
                        'period'             => $info['ordered_time'],
                        'order_id'           => $info['order_id'],
                        'store_id'           => $info['store_id'],
                        'product_id'         => $info['product_id'],
                        'product_sku'        => $info['product_sku'],
                        'product_name'       => $info['product_name'],
                        'category_name'      => $info['category_name'],
                        'product_brand_email'=> $info['representative_email'] ?
                            $this->convertEmailToSimpleString($info['representative_email']) :
                            null,
                        'qty_ordered'        => $info['ordered_qty'],
                        'brand_id'           => $info['brand_id'],
                        'brand_name'         => $info['brand_name'],
                        'product_options'    => $info['product_options'],
                        'company_name'       => $info['company_name'],
                        'address'            => $info['address'],
                        'city'               => $info['city'],
                        'province'           => $info['province'],
                        'product_type'       => $info['product_type'],
                    ];
                }
            }

            $tableName = $this->resource->getTableName(self::AGGREGATION_DAILY);
            //Break down array to prevent large data query, heap size excess
            foreach (array_chunk($insertBatches, 100) as $batch) {
                $connection->insertMultiple($tableName, $batch);
            }

            $this->updateReportMonthlyYearly(
                $connection,
                'month',
                'qty_ordered',
                $mainTable,
                $this->getTable(self::AGGREGATION_MONTHLY)
            );
            $this->updateReportMonthlyYearly(
                $connection,
                'year',
                'qty_ordered',
                $mainTable,
                $this->getTable(self::AGGREGATION_YEARLY)
            );

            $this->_setFlagData(Flag::REPORT_BRANDSALESREPORT_FLAG_CODE);
        } catch (Exception $e) {
            //If exception, truncate all report table
            $this->truncateTable();
            $this->logger->critical($e);
            throw new CouldNotSaveException(__("Could not save report to aggregate table. Please review the log!"));
        }

        return $this;
    }

    /**
     * @param string $emailSerialized
     * @return string
     */
    public function convertEmailToSimpleString(string $emailSerialized): string
    {
        $allEmailBrand = [];
        $arraySimplified = $this->json->unserialize($emailSerialized);
        if (!empty($arraySimplified)) {
            foreach ($arraySimplified as $emailPerBrand) {
                foreach ($emailPerBrand as $email) {
                    $allEmailBrand[] = $email;
                }
            }
            return implode(',', $allEmailBrand);
        }
        return '';
    }

    /**
     * Clean old data before update new data
     */
    public function truncateTable()
    {
        $tables = [
            $this->resource->getTableName(self::AGGREGATION_DAILY),
            $this->resource->getTableName(self::AGGREGATION_MONTHLY),
            $this->resource->getTableName(self::AGGREGATION_YEARLY),
        ];
        $connection = $this->resource->getConnection();

        foreach ($tables as $table) {
            $connection->truncateTable($table);
        }
    }

    /**
     * @param $connection
     * @param $type
     * @param $column
     * @param $mainTable
     * @param $aggregationTable
     * @return $this
     */
    public function updateReportMonthlyYearly($connection, $type, $column, $mainTable, $aggregationTable)
    {
        $periodSubSelect = $connection->select();
        $ratingSubSelect = $connection->select();
        $ratingSelect = $connection->select();

        switch ($type) {
            case 'year':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-01-01');
                break;
            case 'month':
                $periodCol = $connection->getDateFormatSql('t.period', '%Y-%m-01');
                break;
            default:
                $periodCol = 't.period';
                break;
        }

        $columns = [
            'period' => 't.period',
            'order_id' => 't.order_id',
            'store_id' => 't.store_id',
            'product_id' => 't.product_id',
            'product_sku' => 't.product_sku',
            'product_name' => 't.product_name',
            'category_name' => 't.category_name',
            'product_brand_email' => 't.product_brand_email',
            'brand_id' => 't.brand_id',
            'brand_name' => 't.brand_name',
            'product_options' => 't.product_options',
            'company_name' => 't.company_name',
            'address' => 't.address',
            'city' => 't.city',
            'province' => 't.province',
            'product_type' => 't.product_type'
        ];

        if ($type === 'day') {
            $columns['id'] = 't.id';  // to speed-up insert on duplicate key update
        }

        $cols = array_keys($columns);
        $cols['total_qty'] = new Zend_Db_Expr('SUM(t.' . $column . ')');
        $periodSubSelect->from(
            ['t' => $mainTable],
            $cols
        )->group(
            ['t.store_id', $periodCol, 't.product_id', 't.order_id']
        )->order(
            ['t.store_id', $periodCol, 'total_qty DESC']
        );

        $cols = $columns;
        $cols[$column] = 't.total_qty';

        $cols['prevStoreId'] = new Zend_Db_Expr('(@prevStoreId := t.`store_id`)');
        $cols['prevPeriod'] = new Zend_Db_Expr("(@prevPeriod := {$periodCol})");
        $ratingSubSelect->from($periodSubSelect, $cols);

        $cols = $columns;
        $cols['period'] = $periodCol;
        $cols[$column] = 't.' . $column;

        $ratingSelect->from($ratingSubSelect, $cols);

        $sql = $ratingSelect->insertFromSelect($aggregationTable, array_keys($cols));
        $connection->query("SET @pos = 0, @prevStoreId = -1, @prevPeriod = '0000-00-00'");
        $connection->query($sql);
        return $this;
    }
}
