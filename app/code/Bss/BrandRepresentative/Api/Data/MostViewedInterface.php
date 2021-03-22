<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Api\Data;

interface MostViewedInterface
{
    const ID = 'id';
    const ENTITY_ID = 'entity_id';
    const ENTITY_TYPE = 'entity_type';
    const TRAFFIC = 'traffic';

    const TYPE_PRODUCT = 1;
    const TYPE_CATEGORY = 2;

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
    public function getEntityId();

    /**
     * Set brand category id
     *
     * @param int $val
     * @return $this
     */
    public function setEntityId($val);

    /**
     * Get type of entity
     *
     * @return int
     */
    public function getEntityType();

    /**
     * Set type of entity
     *
     * @param int $val
     * @return $this
     */
    public function setEntityType($val);

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
