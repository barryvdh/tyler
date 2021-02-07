<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

/**
 * Class OrderedProduct
 * Get total ordered product qty by customer
 */
class OrderedProduct extends AbstractDb
{
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
                ));
            $select->columns(
                [
                    "total_qty" => new \Zend_Db_Expr("SUM(qty_shipped)")
                ]
            );

            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
