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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderRestriction\Api\Data;

/**
 * Interface RefundItemInterface
 */
interface RefundItemInterface
{
    const ID = "id";
    const ORDER_ID = "order_id";
    const CUSTOMER_ID = "customer_id";
    const PRODUCT_ID = "product_id";
    const QTY = "qty";

    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set id
     *
     * @param int $val
     * @return $this
     */
    public function setId($val);

    /**
     * Get order id
     *
     * @return int
     */
    public function getOrderId();

    /**
     * @param int $val
     * @return $this
     */
    public function setOrderId($val);

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set customer id
     *
     * @param int $val
     * @return $this
     */
    public function setCustomerId($val);

    /**
     * Get product id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product id
     *
     * @param int $val
     * @return $this
     */
    public function setProductId($val);

    /**
     * Get refund qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Set refund qty
     *
     * @param float $val
     * @return $this
     */
    public function setQty($val);
}
