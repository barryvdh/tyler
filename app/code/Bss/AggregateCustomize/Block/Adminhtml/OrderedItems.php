<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml;

use Magento\Catalog\Helper\ImageFactory;

/**
 * Class OrderedItems
 * Show deleted product in ordered items column
 */
class OrderedItems extends \Bss\AdminPreview\Block\Adminhtml\OrderedItems
{
    /**
     * @var ImageFactory
     */
    protected $helperImageFactory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;

    /**
     * OrderedItems constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Bss\AdminPreview\Helper\Data $_dataHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItem
     * @param ImageFactory $helperImageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Sales\Model\Order $order,
        \Bss\AdminPreview\Helper\Data $_dataHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Sales\Model\Order\ItemFactory $orderItem,
        ImageFactory $helperImageFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {
        parent::__construct($context, $imageFactory, $order, $_dataHelper, $priceHelper, $orderItem, $data);
        $this->helperImageFactory = $helperImageFactory;
        $this->assetRepo = $assetRepo;
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
            if (!$orderItem || !$orderItem->getProductId()) {
                continue;
            }
            $product = $orderItem->getProduct();
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
                        $this->processSku(
                            $columnsHtml,
                            $product,
                            $parentPersistent,
                            $_parentItemId,
                            $itemProductType,
                            $orderItem,
                            $key
                        );
                        break;
                    case 'name':
                        $this->processName(
                            $columnsHtml,
                            $product,
                            $parentPersistent,
                            [
                                'parent' => $parentProductType,
                                'item' => $itemProductType
                            ],
                            [
                                'parent' => $_parentItem,
                                'item' => $orderItem
                            ],
                            $key,
                            $storeId
                        );
                        break;
                    case 'image':
                        $this->processItemImage(
                            $columnsHtml,
                            $product,
                            $parentPersistent,
                            [
                                'item' => $orderItem,
                                'parent' => $_parentItem
                            ],
                            $key,
                            $storeId
                        );
                        break;

                    case 'original_price':
                        $this->processOriginPrice(
                            $columnsHtml,
                            $product,
                            $parentPersistent,
                            [
                                'item' => $orderItem,
                                'parent' => $_parentItem
                            ],
                            $key
                        );
                        break;

                    case 'price':
                        $this->processPrice(
                            $columnsHtml,
                            $parentPersistent,
                            [
                                'item' => $orderItem,
                                'parent' => $_parentItem
                            ],
                            $key
                        );
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
     * Process price
     *
     * @param array $columnsHtml
     * @param bool $parentPersistent
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @param string $colKey
     */
    protected function processPrice(
        &$columnsHtml,
        $parentPersistent,
        $items,
        $colKey
    ) {
        $price = $this->formatPrice($items['item']->getPrice());
        if ($parentPersistent) {
            if ($items['parent']->getProductType() == 'configurable') {
                $price = $this->formatPrice($items['parent']->getPrice());
            }
            $price = $this->renderTdElementPreview($items['parent']->getItemId(), $price);
        } else {
            $price = $this->renderTdElementProductType(
                $items['item']->getProductType(),
                $items['item']->getItemId(),
                $price
            );
        }

        array_push($columnsHtml[$colKey], $price);
    }

    /**
     * Process Origin price
     *
     * @param array $columnsHtml
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $parentPersistent
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @param string $colKey
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function processOriginPrice(
        &$columnsHtml,
        $product,
        $parentPersistent,
        $items,
        $colKey
    ) {
        $original_price = $this->formatPrice($items['item']->getOriginalPrice());
        if ($parentPersistent) {
            if ($items['parent']->getProductType() == 'configurable') {
                $original_price = $this->formatPrice($items['parent']->getOriginalPrice());
            }
            $original_price = $this->renderTdElementPreview($items['parent']->getItemId(), $original_price);
        } else {
            $original_price = $this->renderTdElementProductType(
                $items['item']->getProductType(),
                $items['item']->getItemId(),
                $original_price
            );
        }
        array_push($columnsHtml[$colKey], $original_price);
    }

    /**
     * Process item image
     *
     * @param array $columnsHtml
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $parentPersistent
     * @param \Magento\Sales\Model\Order\Item[] $items
     * @param string $colKey
     * @param int $storeId
     */
    protected function processItemImage(
        &$columnsHtml,
        $product,
        $parentPersistent,
        $items,
        $colKey,
        $storeId
    ) {
        if (!$product) {
            $image = $this->getPlaceHolderImgThumbs($items['item']);
        } else {
            if ($storeId) {
                $image = $this->getProductImageSafe($product, $storeId);
            } else {
                $image = $this->getProductImageSafe($product);
            }
        }

        if ($parentPersistent) {
            $image = $this->renderTdElementPreview($items['parent']->getItemId(), $image);
        } else {
            $image = $this->renderTdElementProductType(
                $items['item']->getProductType(),
                $items['item']->getItemId(),
                $image
            );
        }

        array_push($columnsHtml[$colKey], $image);
    }

    /**
     * Process product name column
     *
     * @param array $columnsHtml
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $parentPersistent
     * @param array $productTypes
     * @param array $items
     * @param string $columnKey
     * @param int|null $storeId
     */
    protected function processName(
        &$columnsHtml,
        $product,
        $parentPersistent,
        $productTypes,
        $items,
        $columnKey,
        $storeId
    ) {
        if ($parentPersistent) {
            if (!$product) {
                $name = $items['item']->getName();
            } elseif ($productTypes['parent'] == 'bundle') {
                $name = $this->getProductUrlSafe($product, $storeId, null);
            } else {
                $name = $this->getProductUrlSafe($product, $storeId, $items['parent']);
            }
            $name = $this->renderTdElementPreview($items['parent']->getItemId(), $name);
        } else {
            if (!$product) {
                $name = $items['item']->getName();
            } elseif ($productTypes['item'] == 'grouped') {
                $options = $items['item']->getProductOptions();
                $parentId = $options['super_product_config']['product_id'];
                $name = $this->getProductUrlSafe($product, $storeId, $parentId);
            } else {
                $name = $this->getProductUrlSafe($product, $storeId, null);
            }
            $name = $this->renderTdElementProductType($productTypes['item'], $items['item']->getItemId(), $name);
        }

        array_push($columnsHtml[$columnKey], $name);
    }

    /**
     * Process sku
     *
     * @param array $columnsHtml
     * @param mixed $product
     * @param mixed $parentPersistent
     * @param int $parentItemId
     * @param string $itemProductType
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param string $columnKey
     */
    protected function processSku(
        &$columnsHtml,
        $product,
        $parentPersistent,
        $parentItemId,
        $itemProductType,
        $orderItem,
        $columnKey
    ) {
        $sku = $orderItem->getSku();

        if ($product) {
            $sku = $this->getProductSku($product);
        }

        if ($parentPersistent) {
            $sku = $this->renderTdElementPreview($parentItemId, $sku);
        } else {
            $sku = $this->renderTdElementProductType($itemProductType, $orderItem->getItemId(), $sku);
        }

        array_push($columnsHtml[$columnKey], $sku);
    }

    /**
     * Get placeholder thumb image
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    protected function getPlaceHolderImgThumbs($item)
    {
        $imagePlaceholder = $this->helperImageFactory->create();
        $image = $this->_assetRepo->getUrl($imagePlaceholder->getPlaceholder('small_image'));

        return '<img src="' . $image . '" alt="' . $item->getName() . '"/>';
    }

    /**
     * Load ordered item
     *
     * @param int $id
     * @return \Magento\Sales\Model\Order\Item
     */
    private function loadItem($id)
    {
        return $this->orderItem->create()->load($id);
    }
}
