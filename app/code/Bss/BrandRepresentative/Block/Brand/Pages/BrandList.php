<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Block\Brand\Pages;

use Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Bss\BrandRepresentative\Helper\Data as Helper;

/**
 * Class BrandList - brand list page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandList extends Template
{
    const BRAND_CATEGORY_LEVEL_IDENTIFIER = 3;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ImageFactory
     */
    protected $helperImageFactory;

    /**
     * @var \Magento\Catalog\Model\Category\Image
     */
    private $categoryImage;

    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * @var Toolbar
     */
    private $brandToolbar;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * BrandList constructor.
     *
     * @param ImageFactory $helperImageFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Template\Context $context
     * @param Toolbar $brandToolbar
     * @param \Magento\Catalog\Model\Category\Image $categoryImage
     * @param OutputHelper $outputHelper
     * @param StoreManagerInterface $storeManager
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        ImageFactory $helperImageFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        Template\Context $context,
        \Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar $brandToolbar,
        \Magento\Catalog\Model\Category\Image $categoryImage,
        OutputHelper $outputHelper,
        StoreManagerInterface $storeManager,
        Helper $helper,
        array $data = []
    ) {
        $this->helperImageFactory = $helperImageFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->brandToolbar = $brandToolbar;
        $this->categoryImage = $categoryImage;
        $this->outputHelper = $outputHelper;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get category image
     *
     * @param \Magento\Catalog\Model\Category $subBrand
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoryImage($subBrand)
    {
        if ($imgHtml = $this->categoryImage->getUrl($subBrand)) {
            $imgHtml = $this->outputHelper->categoryAttribute($subBrand, $imgHtml, 'image');
        } else {
            $imgHtml = $this->preparePlaceholder();
            $imgHtml = $this->outputHelper->categoryAttribute($subBrand, $imgHtml, 'image');
        }

        return $imgHtml;
    }

    /**
     * Prepare placeholder for none img category
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function preparePlaceholder(): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'catalog/product/placeholder/' .
            $this->storeManager->getStore()->getConfig('catalog/placeholder/image_placeholder');
    }

    /**
     * Get list category by filter
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|null
     */
    public function getCategories()
    {
        try {
            $categoryCollection = $this->prepareBrandCollection();

            if (!$categoryCollection) {
                return null;
            }

            if ($featuredBrandIds = $this->helper->getFeaturedBrandIds()) {
                $categoryCollection->addAttributeToFilter('entity_id', ['nin' => $featuredBrandIds]);
            }

            if ($this->brandToolbar->getCurrentOrder() === 'most_viewed') {
                $orderExpr = new \Zend_Db_Expr('traffic IS NULL asc, traffic desc');
                $categoryCollection->getSelect()->order([
                    $orderExpr
                ]);
            } elseif ($this->brandToolbar->getCurrentOrder() == "created_at") {
                $categoryCollection->setOrder(
                    $this->brandToolbar->getCurrentOrder(),
                    "desc"
                );
            } else {
                $categoryCollection->setOrder(
                    $this->brandToolbar->getCurrentOrder(),
                    $this->brandToolbar->getCurrentDirection()
                );
            }

            return $categoryCollection;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return null;
    }

    /**
     * Prepare brand collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|null
     */
    protected function prepareBrandCollection()
    {
        try {
            return $this->categoryCollectionFactory->create()
                ->setStore($this->_storeManager->getStore())
                ->addFieldToSelect('*')
                ->addAttributeToFilter('level', self::BRAND_CATEGORY_LEVEL_IDENTIFIER)
                ->addAttributeToFilter('is_active', 1)
                ->setCurPage($this->getCurPage())
                ->setPageSize($this->getPageSize());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return null;
    }

    /**
     * Get current page
     *
     * @return int
     */
    protected function getCurPage()
    {
        return $this->brandToolbar->getCurrentPage();
    }

    /**
     * Get toolbar html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getToolbarHtml()
    {
        $toolbar = $this->getLayout()->getBlock('brand.grid.toolbar');

        if (!$toolbar) {
            $toolbar = $this->getLayout()
                ->createBlock(
                    Toolbar::class,
                    "brand.grid.toolbar"
                )->setCollection($this->getCategories());

            $toolbar->addChild(
                "product_list_toolbar_pager",
                \Magento\Theme\Block\Html\Pager::class
            );
        }

        return $toolbar->toHtml();
    }

    /**
     * Get small place holder image
     *
     * @return string
     */
    public function getPlaceHolderImage(): string
    {
        /** @var Image $helper */
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->_assetRepo->getUrl($imagePlaceholder->getPlaceholder('small_image'));
    }

    /**
     * Get page size
     *
     * @return int
     */
    protected function getPageSize()
    {
        return Toolbar::DEFAULT_LIMIT;
    }
}
