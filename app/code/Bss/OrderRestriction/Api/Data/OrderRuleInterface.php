<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Api\Data;

/**
 * Interface OrderRuleInterface
 */
interface OrderRuleInterface
{
    const ID = "entity_id";
    const PRODUCT_ID = "product_id";
    const SALE_QTY_PER_MONTH = "sale_qty_per_month";
    const USE_CONFIG = "use_config_sale_qty_per_month";

    /**
     * Get order rule item id
     *
     * @return int
     */
    public function getId();

    /**
     * Set order rule item id
     *
     * @param int $val
     * @return $this
     */
    public function setId($val);

    /**
     * Get related product id
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
     * Retrieve the number of products allowed to order in a month
     *
     * @return int
     */
    public function getSaleQtyPerMonth();

    /**
     * Set the number of products allowed to order in a month
     *
     * @param int $val
     * @return $this
     */
    public function setSaleQtyPerMonth($val);

    /**
     * Is use config setting
     *
     * @return int
     */
    public function getUseConfig();

    /**
     * Set is use config
     *
     * @param int $val
     * @return $this
     */
    public function setUseConfig($val);
}
