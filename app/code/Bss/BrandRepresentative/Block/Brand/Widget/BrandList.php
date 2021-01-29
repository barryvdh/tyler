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

namespace Bss\BrandRepresentative\Block\Brand\Widget;

use Exception;
use Magento\Catalog\Block\Product\Widget\Html\Pager;
use Magento\Catalog\Helper\Category;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class BrandList
 *
 * Bss\BrandRepresentative\Block\Brand\Widget
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandList extends Template implements BlockInterface
{
    const BRAND_CATEGORY = 3;
    const ALL_BRAND_PAGE_SIZE = 20;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    /**
     * @var Repository
     */
    protected $assetRepos;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var ImageFactory
     */
    protected $helperImageFactory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_directory;

    /**
     * @var Pager
     */
    protected $pager;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    //Default page size value
    public const PAGE_DEFAULT_FEATURE_TITLE = 'Featured Brands';

    /**
     * BrandList constructor.
     * @param Context $context
     * @param Category $category
     * @param CategoryRepository $categoryRepository
     * @param Conditions $conditionsHelper
     * @param Repository $assetRepos
     * @param ImageFactory $helperImageFactory
     * @param AdapterFactory $imageFactory
     * @param Filesystem $filesystem
     * @param Pager $pager
     * @param CollectionFactory $categoryCollectionFactory
     * @param array $data
     * @throws FileSystemException
     * @suppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Category $category,
        CategoryRepository $categoryRepository,
        Conditions $conditionsHelper,
        Repository $assetRepos,
        ImageFactory $helperImageFactory,
        AdapterFactory $imageFactory,
        Filesystem $filesystem,
        Pager $pager,
        CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->pager = $pager;
        $this->storeManager = $context->getStoreManager();
        $this->category = $category;
        $this->categoryRepository = $categoryRepository;
        $this->conditionsHelper = $conditionsHelper;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        $this->imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return array|mixed|null
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', self::PAGE_DEFAULT_FEATURE_TITLE);
        }
        return $this->getData('title');
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
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('small_image'));
    }

    /**
     * @return array|\Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getFeaturesCategory()
    {
        try {
            $categoryIds = $this->getCategoryIds();
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
            $categoryCollection = $this->categoryCollectionFactory->create()
                ->setStore($this->_storeManager->getStore());
            $categoryCollection->addAttributeToFilter([
                [
                    'attribute' => 'level',
                    'gteq' => self::BRAND_CATEGORY
                ]
            ]);

            if (!empty($categoryIds)) {
                $ids = explode(',', $categoryIds);
                $categoryCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
            }
            $categoryCollection->setPageSize($this->getPageSize());

            return $categoryCollection;
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return [];
    }

    /**
     * @param $imageName string
     * @param $width
     * @param $height
     * @return false|string
     * @throws FileSystemException
     */
    public function getResize(string $imageName, $width = 258, $height = 200)
    {
        $realPath = $this->_filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('catalog/category/' . $imageName);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }
        $targetDir = $this->_filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('resized/' . $width . 'x' . $height);
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        try {
            $image->save($dest);
            if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
                return $this->storeManager->getStore()
                        ->getBaseUrl(
                            UrlInterface::URL_TYPE_MEDIA
                        ) . 'resized/' . $width . 'x' . $height . '/' . $imageName;
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return false;
    }

    /**
     * Retrieve category ids from widget
     *
     * @return string
     */
    public function getCategoryIds()
    {
        $conditions = $this->getData('conditions') ?: $this->getData('conditions_encoded') ?? [];

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        foreach ($conditions as $condition) {
            if (!empty($condition['attribute']) && $condition['attribute'] === 'category_ids') {
                return $condition['value'];
            }
        }
        return '';
    }

    /**
     * Get Page Html Bss
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPagerHtmlBss(): string
    {
        $this->pager = $this->getLayout()->createBlock(
            Pager::class,
            'widget.brands.list.pager'
        )->setCollection($this->getFeaturesCategory());
        $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(true)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit((int)$this->getPageSize())
                    ->setTotalLimit($this->getFeaturesCategory()->getSize());
        if ($this->pager instanceof AbstractBlock) {
            return $this->pager->toHtml();
        }

        return '';
    }

    /**
     * Get widget page size
     *
     * @return int
     */
    private function getPageSize()
    {
        return $this->getData('page_size') ?: self::ALL_BRAND_PAGE_SIZE;
    }
}
