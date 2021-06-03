<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Service;

use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Product\Image\ParamsBuilder;
use Magento\Catalog\Model\Product\Media\ConfigInterface as MediaConfig;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Image;
use Magento\Framework\View\ConfigInterface as ViewConfig;
use Magento\MediaStorage\Helper\File\Storage\Database as FileStorageDatabase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Theme\Model\Config\Customization as ThemeCustomizationConfig;
use Magento\Theme\Model\ResourceModel\Theme\Collection as ThemeCollection;
use Magento\Theme\Model\Theme;
use Magento\Catalog\Model\View\Asset\ImageFactory as AssertImageFactory;
use Magento\Framework\Image\Factory as ImageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ResizeService
 * Resize image service for specific image
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class ResizeImageService
{
    private $viewImages = [];

    /**
     * @var FileStorageDatabase
     */
    private $fileStorageDatabase;

    /**
     * @var ThemeCollection
     */
    private $themeCollection;

    /**
     * @var MediaConfig
     */
    private $imageConfig;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ThemeCustomizationConfig
     */
    private $themeCustomizationConfig;

    /**
     * @var ViewConfig
     */
    private $viewConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ParamsBuilder
     */
    private $paramsBuilder;

    /**
     * @var AssertImageFactory
     */
    private $assertImageFactory;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ResizeImageService constructor.
     *
     * @param MediaConfig $mc
     * @param Filesystem $filesystem
     * @param FileStorageDatabase $fileStorageDatabase
     * @param ThemeCollection $themeCollection
     * @param ThemeCustomizationConfig $themeCustomizationConfig
     * @param StoreManagerInterface $storeManager
     * @param ViewConfig $viewConfig
     * @param ParamsBuilder $paramsBuilder
     * @param AssertImageFactory $assertImageFactory
     * @param ImageFactory $imageFactory
     * @param LoggerInterface $logger
     * @throws \Magento\Framework\Exception\FileSystemException
     * @SuppressWarnings(ExcessiveParameterList)
     */
    public function __construct(
        MediaConfig $mc,
        Filesystem $filesystem,
        FileStorageDatabase $fileStorageDatabase,
        ThemeCollection $themeCollection,
        ThemeCustomizationConfig $themeCustomizationConfig,
        StoreManagerInterface $storeManager,
        ViewConfig $viewConfig,
        ParamsBuilder $paramsBuilder,
        AssertImageFactory $assertImageFactory,
        ImageFactory $imageFactory,
        LoggerInterface $logger
    ) {
        $this->imageConfig = $mc;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileStorageDatabase = $fileStorageDatabase;
        $this->themeCollection = $themeCollection;
        $this->themeCustomizationConfig = $themeCustomizationConfig;
        $this->storeManager = $storeManager;
        $this->viewConfig = $viewConfig;
        $this->paramsBuilder = $paramsBuilder;
        $this->assertImageFactory = $assertImageFactory;
        $this->imageFactory = $imageFactory;
        $this->logger = $logger;
    }

    /**
     * Get view images
     *
     * @param array $themes
     * @return array
     */
    public function getViewImages($themes)
    {
        if (empty($this->viewImages)) {
            $viewImages = [];
            $stores = $this->storeManager->getStores(true);
            /** @var Theme $theme */
            foreach ($themes as $theme) {
                $config = $this->viewConfig->getViewConfig(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'themeModel' => $theme,
                    ]
                );
                $images = $config->getMediaEntities('Magento_Catalog', ImageHelper::MEDIA_TYPE_CONFIG_NODE);
                foreach ($images as $imageId => $imageData) {
                    foreach ($stores as $store) {
                        $data = $this->paramsBuilder->build($imageData, (int) $store->getId());
                        $uniqIndex = $this->getUniqueImageIndex($data);
                        $data['id'] = $imageId;
                        $viewImages[$uniqIndex] = $data;
                    }
                }
            }
            return $viewImages;
        }

        return $this->viewImages;
    }

    /**
     * Get unique image index.
     *
     * @param array $imageData
     * @return string
     */
    private function getUniqueImageIndex(array $imageData): string
    {
        ksort($imageData);
        unset($imageData['type']);
        // phpcs:disable Magento2.Security.InsecureFunction
        return md5(json_encode($imageData));
    }

    /**
     * Search the current theme.
     *
     * @return array
     */
    private function getThemesInUse(): array
    {
        $themesInUse = [];
        $registeredThemes = $this->themeCollection->loadRegisteredThemes();
        $storesByThemes = $this->themeCustomizationConfig->getStoresByThemes();
        $keyType = is_integer(key($storesByThemes)) ? 'getId' : 'getCode';
        foreach ($registeredThemes as $registeredTheme) {
            if (array_key_exists($registeredTheme->$keyType(), $storesByThemes)) {
                $themesInUse[] = $registeredTheme;
            }
        }
        return $themesInUse;
    }

    /**
     * Resize specific file
     *
     * @param string $filePath
     */
    public function resizeFilePath(string $filePath)
    {
        $viewImages = $this->getViewImages($this->getThemesInUse());
        $mediastoragefilename = $this->imageConfig->getMediaPath($filePath);
        $originalImagePath = $this->mediaDirectory->getAbsolutePath($mediastoragefilename);

        if ($this->fileStorageDatabase->checkDbUsage()) {
            $this->fileStorageDatabase->saveFileToFilesystem($mediastoragefilename);
        }
        try {
            if ($this->mediaDirectory->isFile($originalImagePath)) {
                foreach ($viewImages as $viewImage) {
                    $this->resize($viewImage, $originalImagePath, $filePath);
                }
            } else {
                $this->logger->warning(sprintf("Bss resize image service: file %s not found!", $filePath));
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                "ERROR: In file " . self::class . " on function: resizeFilePath. Detail: " .
                $e
            );
        }
    }

    /**
     * Resize image if not already resized before
     *
     * @param array $imageParams
     * @param string $originalImagePath
     * @param string $originalImageName
     * @throws \Exception
     */
    public function resize(array $imageParams, string $originalImagePath, string $originalImageName)
    {
        unset($imageParams['id']);
        $imageAsset = $this->assertImageFactory->create(
            [
                'miscParams' => $imageParams,
                'filePath' => $originalImageName,
            ]
        );
        $imageAssetPath = $imageAsset->getPath();
        $usingDbAsStorage = $this->fileStorageDatabase->checkDbUsage();
        $mediaStorageFilename = $this->mediaDirectory->getRelativePath($imageAssetPath);

        $alreadyResized = $usingDbAsStorage ?
            $this->fileStorageDatabase->fileExists($mediaStorageFilename) :
            $this->mediaDirectory->isFile($imageAssetPath);

        if (!$alreadyResized) {
            $this->generateResizedImage(
                $imageParams,
                $originalImagePath,
                $imageAssetPath,
                $usingDbAsStorage,
                $mediaStorageFilename
            );
        }
    }

    /**
     * Generate resized image
     *
     * @param array $imageParams
     * @param string $originalImagePath
     * @param string $imageAssetPath
     * @param bool $usingDbAsStorage
     * @param string $mediaStorageFilename
     * @throws \Exception
     */
    private function generateResizedImage(
        array $imageParams,
        string $originalImagePath,
        string $imageAssetPath,
        bool $usingDbAsStorage,
        string $mediaStorageFilename
    ) {
        $image = $this->makeImage($originalImagePath, $imageParams);

        if ($imageParams['image_width'] !== null && $imageParams['image_height'] !== null) {
            $image->resize($imageParams['image_width'], $imageParams['image_height']);
        }

        if (isset($imageParams['watermark_file'])) {
            if ($imageParams['watermark_height'] !== null) {
                $image->setWatermarkHeight($imageParams['watermark_height']);
            }

            if ($imageParams['watermark_width'] !== null) {
                $image->setWatermarkWidth($imageParams['watermark_width']);
            }

            if ($imageParams['watermark_position'] !== null) {
                $image->setWatermarkPosition($imageParams['watermark_position']);
            }

            if ($imageParams['watermark_image_opacity'] !== null) {
                $image->setWatermarkImageOpacity($imageParams['watermark_image_opacity']);
            }

            $image->watermark($this->getWatermarkFilePath($imageParams['watermark_file']));
        }

        $image->save($imageAssetPath);

        if ($usingDbAsStorage) {
            $this->fileStorageDatabase->saveFile($mediaStorageFilename);
        }
    }

    /**
     * Returns watermark file absolute path
     *
     * @param string $file
     * @return string
     */
    private function getWatermarkFilePath($file)
    {
        $path = $this->imageConfig->getMediaPath('/watermark/' . $file);
        return $this->mediaDirectory->getAbsolutePath($path);
    }

    /**
     * Make image.
     *
     * @param string $originalImagePath
     * @param array $imageParams
     * @return Image
     */
    private function makeImage(string $originalImagePath, array $imageParams): Image
    {
        $image = $this->imageFactory->create($originalImagePath);
        $image->keepAspectRatio($imageParams['keep_aspect_ratio']);
        $image->keepFrame($imageParams['keep_frame']);
        $image->keepTransparency($imageParams['keep_transparency']);
        $image->constrainOnly($imageParams['constrain_only']);
        $image->backgroundColor($imageParams['background']);
        $image->quality($imageParams['quality']);
        return $image;
    }
}
