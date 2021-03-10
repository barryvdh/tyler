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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandRepresentative\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SalesReport Model
 *
 * Data method:
 * @method $this    setOrderId($orderId)
 * @method $this    setStoreId($storeId)
 * @method $this    setProductSku($sku)
 * @method $this    setProductId($productId)
 * @method $this    setProductName($productName)
 * @method $this    setProductType($productType)
 * @method $this    setOrderedQty($qty)
 * @method $this    setOrderedTime($date)
 * @method $this    setCustomerName($customerName)
 * @method $this    setAddress($address)
 * @method $this    setCity($city)
 * @method $this    setProvince($province)
 * @method $this    setRepresentativeEmail($rawData)
 * @method $this    setSentStatus($status)
 * @method $this    setBrand($brandName)
 */
class SalesReport extends AbstractModel implements IdentityInterface
{
    public const CACHE_TAG = 'bss_sales_report';
    public const SENT_STATUS_SENT = 1;
    public const SENT_STATUS_NOT_SEND = 0;
    protected $_cacheTag = 'bss_sales_report';
    protected $_eventPrefix = 'bss_sales_report';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\SalesReport::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getDefaultValues()
    {
        return [];
    }
}
