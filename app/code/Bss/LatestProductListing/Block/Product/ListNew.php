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
 * @package    Bss_LatestProductListing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\LatestProductListing\Block\Product;

use Bss\BrandRepresentative\Model\BrandProcessor;
use Bss\LatestProductListing\Model\ConfigProvider;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Bss\LatestProductListing\Block\Product\ProductList\Toolbar;

/**
 * Listing product
 */
class ListNew extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Smartwave\Porto\Helper\Data
     */
    protected $portoHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Smartwave\Dailydeals\Helper\Data
     */
    protected $dailyHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogHelperOutput;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var TimezoneInterface
     */
    protected $tz;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var Product\Visibility
     */
    protected $productVisibility;

    /**
     * @var BrandProcessor
     */
    protected $brandProcessor;

    /**
     * ListNew constructor.
     *
     * @param \Smartwave\Porto\Helper\Data $portoHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Smartwave\Dailydeals\Helper\Data $dailyHelper
     * @param \Magento\Catalog\Helper\Output $catalogHelperOutput
     * @param Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param ConfigProvider $configProvider
     * @param ResourceConnection $resourceConnection
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param BrandProcessor $brandProcessor
     * @param array $data
     */
    public function __construct(
        \Smartwave\Porto\Helper\Data $portoHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Smartwave\Dailydeals\Helper\Data $dailyHelper,
        \Magento\Catalog\Helper\Output $catalogHelperOutput,
        Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        ConfigProvider $configProvider,
        ResourceConnection $resourceConnection,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        BrandProcessor $brandProcessor,
        array $data = []
    ) {
        $this->tz = $context->getLocaleDate();
        $this->portoHelper = $portoHelper;
        $this->imageHelper = $imageHelper;
        $this->dailyHelper = $dailyHelper;
        $this->catalogHelperOutput = $catalogHelperOutput;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configProvider = $configProvider;
        $this->resourceConnection = $resourceConnection;
        $this->catalogConfig = $catalogConfig;
        $this->productVisibility = $productVisibility;
        $this->brandProcessor = $brandProcessor;
        parent::__construct($context, $data);
    }

    /**
     * Get product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $this->applyTimezoneToConfigTzForQuery();
            $this->productCollection = $this->initializeProductCollection();
            $this->prepareProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * Prepare collection after init collection
     */
    protected function prepareProductCollection()
    {
        $this->applyPeriodFilter();
        $this->productCollection->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            // ->addMinimalPrice()
            // ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setVisibility($this->productVisibility->getVisibleInCatalogIds());
    }

    /**
     * Apply created time period filter for config days
     */
    protected function applyPeriodFilter()
    {
        $period = $this->configProvider->getPeriodTime();
        $from = $this->tz->date(strtotime(sprintf("%s days ago", $period)))->format("Y-m-d");
        $to = $this->tz->date()->format("Y-m-d");
        $createdField = $this->productCollection->getConnection()->getDateFormatSql("`e`.`created_at`", "%Y-%m-%d");
        $this->productCollection->getSelect()->where(
            sprintf(
                "%s >= '%s' AND %s <= '%s'",
                $createdField,
                $from,
                $createdField,
                $to
            )
        )->order(['e.created_at DESC']);
    }

    /**
     * Apply set db timezone to store config timezone to query data
     *
     * @throws \Exception
     */
    public function applyTimezoneToConfigTzForQuery()
    {
        $connection = $this->resourceConnection->getConnection();
        $utc    = $connection->fetchOne('SELECT CURRENT_TIMESTAMP');
        $offset = (new \DateTimeZone($this->tz->getConfigTimezone()))->getOffset(new \DateTime($utc));
        $h      = floor($offset / 3600);
        $m      = floor(($offset - $h * 3600) / 60);
        $offset = sprintf("%02d:%02d", $h, $m);

        if (substr($offset, 0, 1) != "-") {
            $offset = "+" . $offset;
        }

        $connection->query(sprintf("SET time_zone = '%s'", $offset));
    }

    /**
     * Restore to UTC timezone like default
     *
     * @see \Magento\Framework\DB\Adapter\Pdo\Mysql line 429
     */
    public function restoreTimezone()
    {
        $this->resourceConnection->getConnection()->query(sprintf("SET time_zone = '%s'", '+00:00'));
    }

    /**
     * Get catalog helper output object
     *
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogHelperOutput(): \Magento\Catalog\Helper\Output
    {
        return $this->catalogHelperOutput;
    }

    /**
     * Get daily helper object
     *
     * @return \Smartwave\Dailydeals\Helper\Data
     */
    public function getDailyHelper(): \Smartwave\Dailydeals\Helper\Data
    {
        return $this->dailyHelper;
    }

    /**
     * Get image hhelper object
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper(): \Magento\Catalog\Helper\Image
    {
        return $this->imageHelper;
    }

    /**
     * Get porto helper object
     *
     * @return \Smartwave\Porto\Helper\Data
     */
    public function getPortoHelper(): \Smartwave\Porto\Helper\Data
    {
        return $this->portoHelper;
    }

    /**
     * Init product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function initializeProductCollection(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $collection = $this->productCollectionFactory->create();
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Get listing mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return "grid";
    }

    /**
     * Get pager toolbar html
     *
     * @return string
     */
    public function getToolbarHtml(): string
    {
        $toolbar = $this->getLayout()->getBlock('latest.grid.toolbar');

        if (!$toolbar) {
            $toolbar = $this->getLayout()
                ->createBlock(
                    Toolbar::class,
                    "latest.grid.toolbar"
                )->setCollection($this->productCollection);

            $toolbar->addChild(
                "product_list_toolbar_pager",
                \Magento\Theme\Block\Html\Pager::class
            );
        }

        return $toolbar->toHtml();
    }

    /**
     * Get brand from product
     *
     * @param Product $product
     * @return Category|null
     */
    public function getBrand(Product $product): ?Category
    {
        $ids = $product->getCategoryIds();

        foreach ($ids as $categoryId) {
            $categoryId = (int) $categoryId;
            $brand = $this->brandProcessor->getBrand($categoryId);
            if ($brand && $brand->getId()) {
                return $brand;
            }
        }
        return null;
    }
}
