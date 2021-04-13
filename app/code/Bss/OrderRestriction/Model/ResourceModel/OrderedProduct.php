<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

use Bss\OrderRestriction\Helper\ConfigProvider;

/**
 * Class OrderedProduct
 * Get total ordered product qty by customer
 */
class OrderedProduct extends AbstractDb
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * OrderedProduct constructor.
     *
     * @param ConfigProvider $configProvider
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ConfigProvider $configProvider,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->configProvider = $configProvider;
        parent::__construct($logger, $resource);
    }

    /**
     * Get total ordered product qty by customer
     *
     * @param int $customerId
     * @param int $productId
     * @param array $filterDate - ["defaultTz" => "+00:00", "configTz" => "-05:00",
     * "startDate" => "Y-d-m", "endDate" => "Y-d-m"]
     * @return false|string
     */
    public function getTotalOrderedQty($customerId, $productId, $filterDate)
    {
        try {
            $connection = $this->resource->getConnection();

            $select = $connection->select()
                ->from(
                    ['orders' => $this->getTable("sales_order")],
                    []
                )->joinInner(
                    ['items' => $this->getTable("sales_order_item")],
                    "orders.entity_id = items.order_id",
                    []
                )->where(
                    "orders.customer_id = ?",
                    $customerId
                )->where(
                    "product_id = ?",
                    $productId
                )->where(sprintf(
                    "DATE(CONVERT_TZ(orders.created_at, '%s', '%s')) BETWEEN '%s' AND '%s'",
                    $filterDate["defaultTz"],
                    $filterDate["configTz"],
                    $filterDate["startDate"],
                    $filterDate["endDate"]
                ))->where("state != 'canceled' and state != 'closed'");
            $select->columns(
                [
                    "total_qty" => new \Zend_Db_Expr(sprintf("SUM(%s)", $this->getTotalQtyField()))
                ]
            );

            vadu_log($select->assemble());
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Get total order qty field to calculate
     *
     * @return string
     */
    private function getTotalQtyField()
    {
        if ($this->configProvider->isDecreaseStockWhenOrderPlaced()) {
            return "qty_ordered";
        }

        return "qty_shipped";
    }
}
