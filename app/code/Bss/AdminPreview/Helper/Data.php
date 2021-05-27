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

namespace Bss\AdminPreview\Helper;

use Bss\AdminPreview\Plugin\FrontendUrl;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Bss\AdminPreview\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ENABLED = 'bss_adminpreview/general/enable';
    const XML_PATH_PRODUCT_GRID_PREVIEW_COLUMN = 'bss_adminpreview/general/product_grid_preview_column';
    const XML_PATH_CUSTOMER_GRID_LOGIN_COLUMN = 'bss_adminpreview/general/customer_grid_login_column';
    const XML_PATH_DSIABLE_PAGE_CACHE = 'bss_adminpreview/general/disable_page_cache';
    const XML_PATH_PRODUCT_GRID_COLUMNS = 'bss_adminpreview/general/product_grid_columns';
    const XML_PATH_PRODUCT_PREVIEW_TYPE_LINK = 'bss_adminpreview/general/product_preview_type_link';
    const XML_PATH_EDIT_LINKS_FRONTEND_FOR = 'bss_adminpreview/general/backend_edit_links';
    const XML_PATH_SESSION_TIMEOUT = 'admin/security/session_lifetime';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param FrontendUrl $frontendUrl
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        FrontendUrl $frontendUrl
    ) {
        $this->product = $product;
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->imageHelper = $imageHelper;
        $this->backendHelper = $backendHelper;
        $this->frontendUrl = $frontendUrl;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnable($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return mixed
     */
    public function getProductGridPreviewColumn()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRODUCT_GRID_PREVIEW_COLUMN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getCustomerGridLoginColumn()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_GRID_LOGIN_COLUMN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function isDisablePageCache()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DSIABLE_PAGE_CACHE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getProductLinkType()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRODUCT_PREVIEW_TYPE_LINK,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getSessionTimeout()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_SESSION_TIMEOUT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $productId
     * @param $store
     * @param $parentId
     * @param null $onlyLink
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductUrl($productId, $store, $parentId, $onlyLink = null)
    {
        $product = $this->product->create()->setStoreId($store)->load($productId);
        $pLinkType = $this->getProductLinkType();
        $parentProduct = null;
        if ($parentId) {
            $parentProduct = $this->product->create()->setStoreId($store)->load($parentId);
        }
        return $this->getProductUrlByParams($product, $parentProduct, $store, $pLinkType, $onlyLink);
    }

    /**
     * @param $product
     * @param $store
     * @param $parentItem
     * @param null $onlyLink
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductUrlSafe($product, $store, $parentItem, $onlyLink = null)
    {
        $pLinkType = $this->getProductLinkType();
        $storeId = $store ? (int)$store : null;
        if ($parentItem instanceof \Magento\Sales\Model\Order\Item) {
            return $this->getProductUrlByParams($product, $parentItem->getProduct(), $storeId, $pLinkType, $onlyLink);
        }
        $productItem = $this->product->create()->setStoreId($storeId)->load($parentItem);
        return $this->getProductUrlByParams($product, $productItem, $storeId, $pLinkType, $onlyLink);
    }

    /**
     * @param $product
     * @param $parentProduct
     * @param $store
     * @param $pLinkType
     * @param $onlyLink
     * @return string
     */
    public function getProductUrlByParams($product, $parentProduct, $store, $pLinkType, $onlyLink)
    {
        $productId = $product->getId();
        $parentId = null;
        if ($parentProduct) {
            $parentId = $parentProduct->getId();
        }
        if ($pLinkType == 'backend') {
            $productUrl = $this->backendHelper->getUrl('catalog/product/edit', ['id' => $productId, 'store' => $store]);
            $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $productUrl . '&quot;)">' . $product->getName() . '</a>';
            if ($parentId) {
                $productUrl = $this->backendHelper->getUrl('catalog/product/edit', ['id' => $parentId, 'store' => $store]);
                $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $productUrl . '&quot;)">' . $product->getName() . '</a>';
            }
        } else { //frontend
            if ($parentId && $parentProduct->getVisibility() != 1 && $parentProduct->getStatus() == 1) {
                $productUrl = $this->frontendUrl->getFrontendUrl()
                    ->getUrl('adminpreview/preview/index', ['product_id' => $parentId, 'store' => $store]);
                $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $productUrl . '&quot;)">' . $product->getName() . '</a>';
            } elseif ($product->getStatus() == 1) {
                $productUrl = $this->frontendUrl->getFrontendUrl()
                    ->getUrl('adminpreview/preview/index', ['product_id' => $productId, 'store' => $store]);
                $name = '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $productUrl . '&quot;)">' . $product->getName() . '</a>';
            } else {
                $productUrl = '';
                $name = $product->getName();
            }
        }
        if ($onlyLink === 1) {
            return $productUrl;
        }
        return $name;
    }

    /**
     * @param $productId
     * @param $store
     * @return string
     */
    public function getProductImage($productId, $store = 0)
    {
        $product = $this->product->create()->load($productId);
        $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
        $image = '<img src="' . $imageHelper->getUrl() . '" alt="' . $product->setStore($store)->getName() . '"/>';
        return $image;
    }

    /**
     * @param $product
     * @param $store
     * @return string
     */
    public function getProductImageSafe($product, $store = 0)
    {
        $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
        $image = '<img src="' . $imageHelper->getUrl() . '" alt="' . $product->setStore($store)->getName() . '"/>';
        return $image;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getProductSku($id)
    {
        $product = $this->product->create()->load($id);
        return $product->getSku();
    }

    /**
     * @return mixed
     */
    public function getColumnsTitle()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PRODUCT_GRID_COLUMNS,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getEditLinksFrontendFor()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_EDIT_LINKS_FRONTEND_FOR,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $type
     * @return bool
     */
    public function showLinkFrontend($type)
    {
        if ($this->getEditLinksFrontendFor() && in_array($type, explode(',', $this->getEditLinksFrontendFor()))) {
            return true;
        }
        return false;
    }
}
