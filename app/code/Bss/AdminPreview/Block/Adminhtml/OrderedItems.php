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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Block\Adminhtml;

use Magento\Catalog\Model\Product;

/**
 * Class OrderedItems
 * @package Bss\AdminPreview\Block\Adminhtml
 */
class OrderedItems extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItem;

    /**
     * OrderedItems constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Bss\AdminPreview\Helper\Data $_dataHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItem
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Sales\Model\Order $order,
        \Bss\AdminPreview\Helper\Data $_dataHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\Order\ItemFactory $orderItem,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->fileSystem = $context->getFilesystem();
        $this->_imageFactory = $imageFactory;
        $this->_dataHelper = $_dataHelper;
        $this->order = $order;
        $this->priceHelper = $priceHelper;
        $this->orderItem = $orderItem;
    }

    /**
     * @param $id
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getOrderItems($id)
    {
        return $this->order->load($id)->getItems();
    }

    /**
     * @param $product
     * @param $store
     * @param $parentItem
     * @param null $onlyLink
     * @return string
     */
    public function getProductUrlSafe($product, $store, $parentItem, $onlyLink = null)
    {
        return $this->_dataHelper->getProductUrlSafe($product, $store, $parentItem, $onlyLink);
    }

    /**
     * @param $product
     * @param $store
     * @return string
     */
    public function getProductImageSafe($product, $store = 0)
    {
        return $this->_dataHelper->getProductImageSafe($product, $store);
    }

    /**
     * @param Product $product
     * @return null|string
     */
    public function getProductSku($product)
    {
        return $product->getSku();
    }

    /**
     * @param $price
     * @return float|string
     */
    public function formatPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getColumnsTitle()
    {
        $arrayTitle = array_flip(explode(',', $this->_dataHelper->getColumnsTitle()));
        foreach ($arrayTitle as $key => $value) {
            $arrayTitle[$key] = ucwords(str_replace('_', ' ', $key));
        }
        return $arrayTitle;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getStoreId($orderId)
    {
        $storeId = $this->order->load($orderId)->getStoreId();
        return $storeId;
    }

    /**
     * @param $order
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    // @codingStandardsIgnoreStart
    public function getProductItemColumnsHtml($order)
    {
        $titles = $this->getColumnsTitle();
        $orderId = $order['entity_id'];
        $orderItems = $this->getOrderItems($orderId);
        //get item product id array
        $childIds = [];
        $arr = [];
        foreach ($orderItems as $key => $item) {
            if ($item->getProductType() == 'configurable') {
                $childIds[$item['product_id']][$key] = [];
                $next = $key + 1;
                $childIds[$item['product_id']][$key] = $next;
                if (!in_array($item['product_id'], $arr)) {
                    $arr[$key] = $item['product_id'];
                }
            } else {
                $arr[$key] = $item['product_id'];
            }
        }
        $ItemIds = [];
        foreach ($arr as $key => $value) {
            array_push($ItemIds, $key);
            foreach ($childIds as $key1 => $value1) {
                if ($key1 == $value) {
                    foreach ($value1 as $v) {
                        array_push($ItemIds, $v);
                    }
                }
            }
        }
        $ItemIds = array_unique($ItemIds);
        $items = [];
        foreach ($ItemIds as $value) {
            $item = $this->loadItem($value);
            array_push($items, $item);
        }
        $columnsHtml = [];
        $storeId = $this->getStoreId($orderId);
        /**
         * @var int $key
         * @var \Magento\Sales\Model\Order\Item $orderItem
         */
        foreach ($items as $key => $orderItem) {
            $_product = $orderItem->getProduct();
            if (!$orderItem || !$_product || !$_product->getId()) {
                continue;
            }
            $_parentItem = $orderItem->getParentItem();
            $_parentItemId = $orderItem->getParentItemId();
            if (!$_parentItem && $_parentItemId) {
                $_parentItem = $this->loadItem($_parentItemId);
            }

            // Declaration all var can be reused
            $itemId = $orderItem->getItemId();
            $itemProductType = $orderItem->getProductType();
            // End

            $columnsHtml[$key] = [];
            $parentProductType = null;
            $parentPersistent = false;

            if ($_parentItemId && $_parentItem) {
                $parentProductType = $_parentItem->getProductType();
                $parentPersistent = true;
            }
            foreach ($titles as $keyTitle => $value) {
                switch ($keyTitle) {
                    case 'sku':
                        $sku = $this->getProductSku($_product);
                        if ($parentPersistent) {
                            $sku = $this->renderTdElementPreview($_parentItemId, $sku);
                        } else {
                            $sku = $this->renderTdElementProductType($itemProductType, $itemId, $sku);
                        }
                        array_push($columnsHtml[$key], $sku);
                        break;
                    case 'name':
                        if ($parentPersistent) {
                            if ($parentProductType == 'bundle') {
                                $name = $this->getProductUrlSafe($_product, $storeId, null);
                            } else {
                                $name = $this->getProductUrlSafe($_product, $storeId, $_parentItem);
                            }
                            $name = $this->renderTdElementPreview($_parentItemId, $name);
                        } else {
                            if ($itemProductType == 'grouped') {
                                $options = $orderItem->getProductOptions();
                                $parentId = $options['super_product_config']['product_id'];
                                $name = $this->getProductUrlSafe($_product, $storeId, $parentId);
                            } else {
                                $name = $this->getProductUrlSafe($_product, $storeId, null);
                            }
                            $name = $this->renderTdElementProductType($itemProductType, $itemId, $name);
                        }
                        array_push($columnsHtml[$key], $name);
                        break;
                    case 'image':
                        if ($storeId && $storeId != null) {
                            $image = $this->getProductImageSafe($_product, $storeId);
                        } else {
                            $image = $this->getProductImageSafe($_product);
                        }
                        if ($parentPersistent) {
                            $image = $this->renderTdElementPreview($_parentItemId, $image);
                        } else {
                            $image = $this->renderTdElementProductType($itemProductType, $itemId, $image);
                        }
                        array_push($columnsHtml[$key], $image);
                        break;

                    case 'original_price':
                        $original_price = $this->formatPrice($orderItem->getOriginalPrice());
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $original_price = $this->formatPrice($_parentItem->getOriginalPrice());
                            }
                            $original_price = $this->renderTdElementPreview($_parentItemId, $original_price);
                        } else {
                            $original_price = $this->renderTdElementProductType($itemProductType, $itemId, $original_price);
                        }
                        array_push($columnsHtml[$key], $original_price);
                        break;

                    case 'price':
                        $price = $this->formatPrice($orderItem->getPrice());
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $price = $this->formatPrice($_parentItem->getPrice());
                            }
                            $price = $this->renderTdElementPreview($_parentItemId, $price);
                        } else {
                            $price = $this->renderTdElementProductType($itemProductType, $itemId, $price);
                        }
                        array_push($columnsHtml[$key], $price);
                        break;

                    case 'qty_ordered':
                        $qty_ordered = round($orderItem->getQtyOrdered());
                        if ($parentPersistent) {
                            $qty_ordered = $this->renderTdElementPreview($_parentItemId, $qty_ordered);
                        } else {
                            $qty_ordered = $this->renderTdElementProductType($itemProductType, $itemId, $qty_ordered, true);
                        }
                        array_push($columnsHtml[$key], $qty_ordered);
                        break;

                    case 'subtotal':
                        $subtotal = $this->formatPrice($orderItem->getRowTotal());
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $subtotal = $this->formatPrice($_parentItem->getRowTotal());
                            }
                            $subtotal = $this->renderTdElementPreview($_parentItemId, $subtotal);
                        } else {
                            $subtotal = $this->renderTdElementProductType($itemProductType, $itemId, $subtotal);
                        }
                        array_push($columnsHtml[$key], $subtotal);
                        break;

                    case 'tax_amount':
                        $tax_amount = $this->formatPrice($orderItem->getTaxAmount());
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $tax_amount = $this->formatPrice($_parentItem->getTaxAmount());
                            }
                            $tax_amount = $this->renderTdElementPreview($_parentItemId, $tax_amount);
                        } else {
                            $tax_amount = $this->renderTdElementProductType($itemProductType, $itemId, $tax_amount);
                        }
                        array_push($columnsHtml[$key], $tax_amount);
                        break;

                    case 'tax_percent':
                        $tax_percent = round($orderItem->getTaxPercent(), 2).'%';
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $tax_percent = round($_parentItem->getTaxPercent(), 2).'%';
                            }
                            $tax_percent = $this->renderTdElementPreview($_parentItemId, $tax_percent);
                        } else {
                            $tax_percent = $this->renderTdElementProductType($itemProductType, $itemId, $tax_percent);
                        }
                        array_push($columnsHtml[$key], $tax_percent);
                        break;

                    case 'row_total_incl_tax':
                        $tax = $orderItem->getTaxAmount() ? : 0;
                        $row_total_incl_tax = $this->formatPrice($orderItem->getRowTotal() - $orderItem->getDiscountAmount() + $tax);
                        if ($parentPersistent) {
                            if ($parentProductType == 'configurable') {
                                $tax = $_parentItem->getTaxAmount() ? $_parentItem->getTaxAmount() : 0;
                            }
                            $row_total_incl_tax = $this->formatPrice($_parentItem->getRowTotal() - $_parentItem->getDiscountAmount() + $tax);
                            $row_total_incl_tax = $this->renderTdElementPreview($_parentItemId, $row_total_incl_tax);
                        } else {
                            $row_total_incl_tax = $this->renderTdElementProductType($itemProductType, $itemId, $row_total_incl_tax);
                        }
                        array_push($columnsHtml[$key], $row_total_incl_tax);
                        break;

                    default:
                        break;
                }
            }
        }
        return $columnsHtml;
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param $productType
     * @return bool
     */
    protected function isBundleOrConfigurable($productType)
    {
        return $productType == 'configurable' || $productType == 'bundle';
    }

    /**
     * @param $productType
     * @param $idClass
     * @param $htmlText
     * @param $forceNullHtmlText
     * @return string
     */
    protected function renderTdElementProductType($productType, $idClass, $htmlText, $forceNullHtmlText = false)
    {
        if ($this->isBundleOrConfigurable($productType)) {
            $htmlText = $forceNullHtmlText ? '' : $htmlText;
            return '<td class="parent-preview-id-' . $idClass . '">' . $htmlText . '</td>';
        }
        return '<td>' . $htmlText . '</td>';
    }

    /**
     * @param $idClass
     * @param $htmlText
     * @return string
     */
    protected function renderTdElementPreview($idClass, $htmlText)
    {
        return '<td class="bss-show-hide-preview-' . $idClass . '">' . $htmlText . '</td>';
    }

    /**
     * @param $id
     * @return \Magento\Sales\Model\Order\Item
     */
    private function loadItem($id)
    {
        return $this->orderItem->create()->load($id);
    }
}
