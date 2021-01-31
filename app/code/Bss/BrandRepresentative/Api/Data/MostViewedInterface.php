<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Api\Data;

interface MostViewedInterface
{
    const ID = 'id';
    const CATEGORY_ID = 'category_id';
    const TRAFFIC = 'traffic';

    /**
     * Get traffic row id
     *
     * @return int
     */
    public function getId();

    /**
     * Set traffic row id
     *
     * @param int $val
     * @return $this
     */
    public function setId($val);

    /**
     * Get brand category id
     *
     * @return int
     */
    public function getCategoryId();

    /**
     * Set brand category id
     *
     * @param int $val
     * @return $this
     */
    public function setCategoryId($val);

    /**
     * Get traffic value
     *
     * @return int
     */
    public function getTraffic();

    /**
     * Set traffic value
     *
     * @param int $val
     * @return $this
     */
    public function setTraffic($val);
}
