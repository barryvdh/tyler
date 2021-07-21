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

namespace Bss\ProductInventoryReport\Model\ResourceModel\ProductInventoryReport;

use Bss\ProductInventoryReport\Model\ResourceModel\ProductInventoryReport;
use Magento\Framework\DataObject;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Product inventory report collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var int[]
     */
    protected $brandIds;

    /**
     * Init resource model and model
     */
    protected function _construct()
    {
        $this->_init(DataObject::class, ProductInventoryReport::class);
    }

    /**
     * Apply brand filter before load
     *
     * @return Collection
     */
    protected function _beforeLoad()
    {
        $this->applyBrandFilter();
        return parent::_beforeLoad();
    }

    /**
     * Query selected brands
     *
     * @param \Magento\Framework\DB\Select|null $select
     * @return $this
     */
    protected function applyBrandFilter(\Magento\Framework\DB\Select $select = null): Collection
    {
        if (empty($this->brandIds) || !is_array($this->brandIds)) {
            return $this;
        }

        if (!$select) {
            $select = $this->getSelect();
        }
        $select->where('`brand_id` IN (?)', $this->brandIds);

        return $this;
    }

    /**
     * Set brand ids for filter
     *
     * @param int[] $brandIds
     * @return $this
     */
    public function setBrandFilter(array $brandIds): Collection
    {
        $this->brandIds = $brandIds;

        return $this;
    }
}
