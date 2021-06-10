<?php
declare(strict_types=1);

namespace Bss\DigitalAssetsManage\Block\Adminhtml\Report;

use Bss\DigitalAssetsManage\Helper\DownloadableHelper;
use Bss\DigitalAssetsManage\Helper\GetBrandDirectory;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\Data\Collection;

/**
 * Class Grid
 * Brand digital assets grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var GetBrandDirectory
     */
    protected $getBrandDirectory;

    /**
     * @var DownloadableHelper
     */
    protected $downloadableHelper;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param GetBrandDirectory $getBrandDirectory
     * @param DownloadableHelper $downloadableHelper
     * @param MediaConfig $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        GetBrandDirectory $getBrandDirectory,
        DownloadableHelper $downloadableHelper,
        MediaConfig $mediaConfig,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->getBrandDirectory = $getBrandDirectory;
        $this->downloadableHelper = $downloadableHelper;
        $this->mediaConfig = $mediaConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set brand colleciton to report
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->getBrandDigitalAssetsStorageCollection());
        return parent::_prepareCollection();
    }

    /**
     * Avoid error in line 246 in extended.phtml template
     *
     * @param \Magento\Framework\DataObject $item
     * @return false
     */
    public function getMultipleRows($item)
    {
        return false;
    }

    /**
     * Get brand collection
     *
     * @return Collection
     */
    protected function getBrandDigitalAssetsStorageCollection()
    {
        $brands = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')->addFieldToFilter("level", ['eq' => 3]);

        return $brands;
    }

    /**
     * Prepare report grid cols
     *
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __("Brand ID"),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Brand Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'storage_amount',
            [
                'header' => __("Storage Amount"),
                'index' => 'storage_amount',
                'sortable' => false,
                'frame_callback' => [$this, "storageCalculate"]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Calculate the size of brand assets
     *
     * @param string $value
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function storageCalculate($value, $category, $column)
    {
        $unitTbl = [
            0 => "Bytes",
            1 => "KB",
            2 => "MB",
            3 => "GB"
        ];

        $unit = $unitTbl[0];
        try {
            if ($category instanceof \Magento\Catalog\Model\Category) {
                $basePaths = [
                    $this->mediaConfig->getBaseMediaPath(),
                    $this->downloadableHelper->getLink()->getBasePath(),
                    $this->downloadableHelper->getLink()->getBaseSamplePath(),
                    $this->downloadableHelper->getSample()->getBasePath()
                ];

                $size = 0;
                foreach ($basePaths as $basePath) {
                    $absoluteBasePath = $this->downloadableHelper->getFilePath(
                        $this->getMediaDirectory()->getAbsolutePath(),
                        $basePath
                    );
                    $finalPath = $this->downloadableHelper->getFilePath(
                        $absoluteBasePath,
                        $this->getBrandDirectory->getBrandPathWithCategory($category)
                    );
                    $size += $this->getSize($finalPath);
                }

                $i = 1;
                while ($size > 1024) {
                    $unit = $unitTbl[$i];
                    $size = $size / 1024;
                    $i++;
                }

                return round($size, 2) . " " . $unit;
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                "BSS - ERROR: When render storage amount. Detail: " . $e
            );
        }

        return 0 . $unitTbl[0];
    }

    /**
     * Get folder size of brand dir
     *
     * @param string $dir
     * @param string $brandName
     * @return int
     */
    protected function folderSizeRecursive(string $dir, string $brandName): int
    {
        $size = 0;

        // @codingStandardsIgnoreStart
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
//            dump($each, is_file($each) && strpos(
//                    $each,
//                    $brandName . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME
//                ) !== false, ['strpos ' => [$each,
//                $brandName . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME]]);


            if (is_file($each) &&
                strpos($each, $this->getDigitalAssetsPath($brandName)) !== false &&
                $this->notInCache($each, $brandName)
            ) {
//                dump(['filesize' => $this->getMediaDirectory()->stat($each)]);
//                vadu_log(['sizeee_nme' => $each]);
                $size += (int) filesize($each);
            } else {
                $size += $this->folderSizeRecursive($each, $brandName);
            }
        }

        return $size;
    }

    /**
     * Get folder size of brand dir
     *
     * @param string $dir
     * @return int
     */
    protected function getSize(string $dir): int
    {
        $dir = rtrim(str_replace('\\', '/', $dir), '/');

        if (is_dir($dir) === true) {
            $totalSize = 0;
            $os        = strtoupper(substr(PHP_OS, 0, 3));
            // If on a Unix Host (Linux, Mac OS)
            if ($os !== 'WIN') {
                $io = popen('/usr/bin/du -sb ' . $dir, 'r');
                if ($io !== false) {
                    $totalSize = intval(fgets($io, 80));
                    pclose($io);
                    return $totalSize;
                }
            }
            // If on a Windows Host (WIN32, WINNT, Windows)
            if ($os === 'WIN' && extension_loaded('com_dotnet')) {
                $obj = new \COM('scripting.filesystemobject');
                if (is_object($obj)) {
                    $ref       = $obj->getfolder($dir);
                    $totalSize = $ref->size;
                    $obj       = null;
                    return $totalSize;
                }
            }
            // If System calls did't work, use slower PHP 5
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
            return $totalSize;
        } else if (is_file($dir) === true) {
            return filesize($dir);
        }

        return 0;
    }

    /**
     * @param string $brandName
     * @return string
     */
    protected function getDigitalAssetsPath(string $brandName): string
    {
        return $brandName . DIRECTORY_SEPARATOR . GetBrandDirectory::DIGITAL_ASSETS_FOLDER_NAME;
    }

    /**
     * Check if current path is not in cache folder
     *
     * @param string $path
     * @param string $brandName
     * @return bool
     */
    private function notInCache(string $path, string $brandName): bool
    {
        preg_match(
            "/cache\/.*$brandName\/" . GetBrandDirectory::DIGITAL_ASSETS_FOLDER_NAME . "/i",
            $path,
            $matchs
        );

        return empty($matchs);
    }

    /**
     * Disable filter
     *
     * @return false
     */
    public function getFilterVisibility()
    {
        return false;
    }
}
