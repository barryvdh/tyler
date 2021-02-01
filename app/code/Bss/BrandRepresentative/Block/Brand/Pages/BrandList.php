<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Block\Brand\Pages;

use Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;

/**
 * Class BrandList - brand list page
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandList extends Template
{
    const BRAND_CATEGORY_LEVEL_IDENTIFIER = 3;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ImageFactory
     */
    protected $helperImageFactory;

    /**
     * @var Toolbar
     */
    private $brandToolbar;

    /**
     * BrandList constructor.
     *
     * @param ImageFactory $helperImageFactory
     * @param AdapterFactory $adapterFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param Template\Context $context
     * @param Toolbar $brandToolbar
     * @param array $data
     */
    public function __construct(
        ImageFactory $helperImageFactory,
        AdapterFactory $adapterFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        Template\Context $context,
        \Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar $brandToolbar,
        array $data = []
    ) {
        $this->helperImageFactory = $helperImageFactory;
        $this->imageFactory = $adapterFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->brandToolbar = $brandToolbar;
        parent::__construct($context, $data);
    }

    /**
     * Get list category by filter
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategories()
    {
        try {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
            $categoryCollection = $this->categoryCollectionFactory->create()
                ->setStore($this->_storeManager->getStore());
            $categoryCollection
                ->addAttributeToSelect('*')
                ->addAttributeToFilter([
                    [
                        'attribute' => 'level',
                        'gteq' => self::BRAND_CATEGORY_LEVEL_IDENTIFIER
                    ]
                ]);

            if ($this->brandToolbar->getCurrentOrder() === 'most_viewed') {
                if (!$this->brandToolbar->getCurrentDirection() ||
                    $this->brandToolbar->getCurrentDirection() === 'asc'
                ) {
                    $orderExpr = new \Zend_Db_Expr('traffic IS NULL desc, traffic asc');
                } else {
                    $orderExpr = new \Zend_Db_Expr('traffic IS NULL asc, traffic desc');
                }

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

            $categoryCollection->setCurPage($this->brandToolbar->getCurrentPage());
            $categoryCollection->setPageSize(Toolbar::DEFAULT_LIMIT);

            return $categoryCollection;
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
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

            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                "product_list_toolbar_pager"
            );
            $toolbar->setChild(
                "product_list_toolbar_pager",
                $pager
            );
        }

        return $toolbar->toHtml();
    }

    /**
     * Get resized category image
     *
     * @param string $imageName
     * @param int $width
     * @param int $height
     * @return false|string
     * @throws FileSystemException
     */
    public function getResize(string $imageName, $width = 258, $height = 200)
    {
        $realPath = $this->directory->getAbsolutePath($imageName);
        $directoryRead = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $directoryWrite = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!$this->directory->isFile($realPath) || !$this->directory->isExist($realPath)) {
            return false;
        }
        $targetDir = $directoryRead->getAbsolutePath('resized/' . $width . 'x' . $height);
        $pathTargetDir = $directoryWrite->getRelativePath($targetDir);
        if (!$this->directory->isExist($pathTargetDir)) {
            $directoryWrite->create($pathTargetDir);
        }
        if (!$directoryWrite->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width, $height);
        // @codingStandardsIgnoreLine
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        try {
            $image->save($dest);
            $imagePath = $directoryWrite->getRelativePath($dest);
            if ($directoryWrite->isFile($directoryWrite->getRelativePath($dest))) {
                return $this->_storeManager->getStore()
                        ->getBaseUrl(
                            UrlInterface::URL_TYPE_MEDIA
                        ) . $imagePath;
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return false;
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
}
