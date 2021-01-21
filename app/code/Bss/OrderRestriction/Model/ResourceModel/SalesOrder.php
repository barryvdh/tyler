<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

/**
 * Class SalesOrder
 */
class SalesOrder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * SalesOrder constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get customer order summary for report chart
     *
     * @param int $customerId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getCustomerOrderSummary($customerId, $startDate, $endDate)
    {
        try {
            $connection = $this->resource->getConnection();

            $select = $connection->select();
            $select->from(
                ['sales_order' => $this->resource->getTableName('sales_order')],
                []
            );
            $select->where('`sales_order`.`customer_id` = ?', $customerId);
            $select->where('`created_at` >= ?', $startDate);
            $select->where('`created_at` <= ?', $endDate);
            $select->columns(
                [
                    'quantity' => 'COUNT(*)',
                    'created_at' => 'DATE(`created_at`)'
                ]
            );
            $select->group('DATE(`created_at`)');

            return $connection->fetchAll($select);
        } catch (\Exception $e) {
            return [];
        }
    }
}
