<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Api\Data;

/**
 * Class OrderRuleInterface
 */
interface OrderRuleInterface
{
    const CUSTOMER_ID = 'customer_id';
    const QTY_PER_ORDER = 'qty_per_order';
    const ORDERS_PER_MONTH = 'orders_per_month';

    /**
     * Get customer who be restrict
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $value
     * @return $this
     */
    public function setCustomerId($value);

    /**
     * Get the number of qty customer place order
     *
     * @return int
     */
    public function getQtyPerOrder();

    /**
     * Set the number of qty customer place order
     *
     * @param int $val
     * @return $this
     */
    public function setQtyPerOrder($val);

    /**
     * Get the number of orders that customer can order/month
     *
     * @return int
     */
    public function getOrdersPerMonth();

    /**
     * Set the number of orders customer can order/month
     *
     * @param int $val
     * @return $this
     */
    public function setOrdersPerMonth($val);
}
