<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Helper;

use Bss\DigitalAssetsManage\Model\Product\Gallery\Processor;
use Bss\DigitalAssetsManage\Service\ResizeImageService;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface as GalleryEntryInterface;
use Magento\Catalog\Model\Product\Gallery\Entry;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class UniqueFileName
 * Get custom digital assets file path
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class UniqueFileName
{
    const DIGITAL_ASSETS_FOLDER_NAME = "DigitalAssets";
    const BRAND_CATEGORY_LV = '3';

    protected $galleryAttribute;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     * @since 101.0.0
     */
    protected $fileStorageDb;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $mediaConfig;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Magento\Framework\File\Mime
     */
    protected $mime;

    /**
     * @var ProductAttributeMediaGalleryManagementInterface
     */
    protected $galleryManagement;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Processor
     */
    protected $galleryProcessor;
    /**
     * @var \Magento\Framework\Api\Data\ImageContentInterfaceFactory
     */
    protected $imageContentInterfaceFactory;
    /**
     * @var ResizeImageService
     */
    protected $resizeImageService;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var GetBrandDirectory
     */
    protected $getBrandDirectory;

    /**
     * UniqueFileName constructor.
     *
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\File\Mime $mime
     * @param ProductAttributeMediaGalleryManagementInterface $galleryManagement
     * @param ResourceConnection $resourceConnection
     * @param Processor $galleryProcessor
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\File\Mime $mime,
        ProductAttributeMediaGalleryManagementInterface $galleryManagement,
        ResourceConnection $resourceConnection,
        Processor $galleryProcessor,
        \Magento\Framework\Api\Data\ImageContentInterfaceFactory $imageContentInterfaceFactory,
        ResizeImageService $resizeImageService,
        EventManager $eventManager,
        GetBrandDirectory $getBrandDirectory
    ) {
        $this->fileStorageDb = $fileStorageDb;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->mime = $mime;
        $this->galleryManagement = $galleryManagement;
        $this->resourceConnection = $resourceConnection;
        $this->galleryProcessor = $galleryProcessor;
        $this->imageContentInterfaceFactory = $imageContentInterfaceFactory;
        $this->resizeImageService = $resizeImageService;
        $this->eventManager = $eventManager;
        $this->getBrandDirectory = $getBrandDirectory;
    }

    /**
     * Get product media path for the brand digital
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file
     * @param bool $forTmp
     * @return string
     */
    public function get($product, $file, $forTmp = false): string
    {
        if ($this->fileStorageDb->checkDbUsage()) {
            $destFile = $this->fileStorageDb->getUniqueFilename(
                $this->mediaConfig->getBaseMediaUrlAddition(),
                $file
            );
        } else {
            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $fileInfo = pathinfo($file);
            $brandPath = $this->getBrandDirectoryPath($product);

            // get brand path for check exist image
            if (isset($fileInfo['basename']) && $brandPath) {
                $brandFilePath = $brandPath . DIRECTORY_SEPARATOR . $fileInfo['basename'];
            }

            $destinationFile = $forTmp
                ? $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getTmpMediaPath($file))
                : $this->mediaDirectory->getAbsolutePath($this->mediaConfig->getMediaPath($brandFilePath ?? $file));

            // phpcs:disable Magento2.Functions.DiscouragedFunction
            $destFile = $brandPath . '/' . FileUploader::getNewFileName($destinationFile);
        }

        return $destFile;
    }

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product $product
     * @param string|null $brandDir
     * @SuppressWarnings(UnusedLocalVariable)
     */
    public function moveProductImagesToBrandDir(
        $product,
        string $brandDir = null
    ) {
        try {
            if ($brandDir === null) {
                $brandDir = $this->getBrandDirectory->execute($product);
            }

            if (!$brandDir) {
                return;
            }

            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
//            $needMoveImages = [];
//            $entryChanged = false;
//            foreach ($existingMediaGalleryEntries as $key => $entry) {
//                if (strpos($entry->getFile(), ltrim($brandDir)) !== false) {
//                    continue;
//                }
////                $needMoveImages[$entry->getId()]['object'] = $entry;
////                unset($existingMediaGalleryEntries[$key]);
////                // move to tmp directory
////                $needMoveImages[$entry->getId()]['brand_tmp_file'] = $this->mediaConfig->getBaseMediaPath() .
////                    $this->moveToBrandDir(
////                        $this->mediaConfig->getBaseMediaPath(),
////                        $brandDir,
////                        $entry->getFile(),
////                        true
////                    );
//
//                $imgPath = $this->mediaConfig->getBaseMediaPath() . $this->moveToBrandDir(
//                    $this->mediaConfig->getBaseMediaPath(),
//                    $brandDir,
//                    $entry->getFile(), true
//                );
//                // phpcs:ignore Magento2.Functions.DiscouragedFunction
//                $pathInfo = pathinfo($imgPath);
//                $destinationFile = $this->getFilePath(
//                    $this->mediaDirectory->getAbsolutePath(
//                        $this->mediaConfig->getBaseMediaPath() . $brandDir
//                    ),
//                    $pathInfo['basename']
//                );
//
//                $destinationFile = $imgPath;
//
//                $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($destinationFile);
//                $imageMimeType = $this->mime->getMimeType($absoluteFilePath);
//                $imageContent = $this->mediaDirectory->readFile($absoluteFilePath);
//                $imageBase64 = base64_encode($imageContent);
//                $imageName = $pathInfo['filename'];
//
//                /** @var ImageContentInterface $imgContent */
//                $imgContent = $this->imageContentInterfaceFactory->create();
//                $imgContent->setName($imageName);
//                $imgContent->setType($imageMimeType);
//                $imgContent->setBase64EncodedData($imageBase64);
//                $entry->setContent($imgContent);
//                $entry->setFile($imgPath);
//                $entryChanged = true;
//            }
//
//            if ($entryChanged) {
//                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
//                $this->productRepository->save($product);
//            }
//
//            return;

//            if (!empty($needMoveImages)) {
//                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
//
//                $needResizePaths = [];
//                foreach ($needMoveImages as $entryData) {
//                    /** @var Entry $entry */
//                    $entry = $entryData['object'];
//                    $movedPath = $this->galleryProcessor->addImage(
//                        $product,
//                        $entryData['brand_tmp_file'],
//                        $entry->getTypes(),
//                        true,
//                        $entry->getData('disabled'),
//                        [
//                            'position' => $entry->getPosition(),
//                            'label' => $entry->getLabel()
//                        ]
//                    );
//
//                    $needResizePaths[] = $movedPath;
//                }
//
//                $this->productRepository->save($product);
//
//                $this->eventManager->dispatch(
//                    "bss_digital_assets_after_move_to_brand_dir",
//                    ['need_resize_images' => $needResizePaths]
//                );
//            }
//            return;

//            $mediaGallery = $product->getData('media_gallery');
//            if (isset($mediaGallery['images'])) {
//                $images = &$mediaGallery['images'];
//                foreach ($images as &$image) {
//                    if (strpos($image['file'], ltrim($brandDir)) !== false) {
//                        continue;
//                    }
//                    $imgPath = $this->moveToBrandDir(
//                        $this->mediaConfig->getBaseMediaPath(),
//                        $brandDir,
//                        $image['file']
//                    );
//
//                    $image['file'] = $imgPath;
//                }
//            }
//
//            $product->setData('media_gallery', $mediaGallery);

//            try {
//                $this->productRepository->save($product);
//            } catch (\Exception $e) {
//                dd($e);
//            }
//            return;
//            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
//            foreach ($existingMediaGalleryEntries as $entry) {
//                if (strpos($entry->getFile(), ltrim($brandDir)) !== false) {
//                    continue;
//                }
//
//                $imgPath = $this->moveToBrandDir(
//                    $this->mediaConfig->getBaseMediaPath(),
//                    $brandDir,
//                    $entry->getFile()
//                );
//
//                $this->updateMediaGalleryEntityVal((int) $entry->getId(), $imgPath);
//            }
//
//            return;
            foreach ($existingMediaGalleryEntries as $key => $entry) {
                if (strpos($entry->getFile(), ltrim($brandDir)) !== false) {
                    continue;
                }
                // vadu_log(['exist' => $entry->getFile()]);
                // $needMoveImages[] = $entry;
                // vadu_log(['img' => $entry->getFile()]);

                $imgPath = $this->moveToBrandDir(
                    $this->mediaConfig->getBaseMediaPath(),
                    $brandDir,
                    $entry->getFile()
                );

                $attrCode = $this->getAttribute()->getAttributeCode();
                $mediaGalleryData = $product->getData($attrCode);

                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $pathInfo = pathinfo($imgPath);
                $destinationFile = $this->getFilePath(
                    $this->mediaDirectory->getAbsolutePath(
                        $this->mediaConfig->getBaseMediaPath() . $brandDir
                    ),
                    $pathInfo['basename']
                );

                $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($destinationFile);
                $imageMimeType = $this->mime->getMimeType($absoluteFilePath);
                $imageContent = $this->mediaDirectory->readFile($absoluteFilePath);
                $imageBase64 = base64_encode($imageContent);
                $imageName = $pathInfo['filename'];

                if (!is_array($mediaGalleryData)) {
                    $mediaGalleryData = ['images' => []];
                }
                $entry->setFile($imgPath);

//                $this->galleryManagement->update($product->getSku(), $entry);
//                $position = $entry->getPosition();
//                $mediaGalleryData['images'][] = [
//                    'file' => $imgPath,
//                    'position' => $position,
//                    'label' => '',
//                    'disabled' => (int) $needMoveImage->getData("disabled"),
//                    'media_type' => 'image',
//                    'types' => $needMoveImage->getTypes(),
//                    'content' => [
//                        'data' => [
//                            ImageContentInterface::NAME => $imageName,
//                            ImageContentInterface::BASE64_ENCODED_DATA => $imageBase64,
//                            ImageContentInterface::TYPE => $imageMimeType,
//                        ]
//                    ]
//                ];

//                $product->setData($attrCode, $mediaGalleryData);

                if ($entry->getTypes() !== null) {
                    $this->setMediaAttribute(
                        $product,
                        $entry->getTypes(),
                        $imgPath
                    );
                }
            }

            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
            try {
                $this->productRepository->save($product);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
            return;
            if ($needMoveImages) {
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                foreach ($needMoveImages as $needMoveImage) {

                }

                try {
                    $product->save();
                } catch (\Exception $e) {

                    $this->logger->critical($e);
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical(
                self::class . "::moveProductImagesToBrandDir: " .
                $e
            );
        }
    }

    /**
     * @param int $id
     * @param string $value
     */
    protected function updateMediaGalleryEntityVal(int $id, string $value)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                ['entity_tbl' => $connection->getTableName('catalog_product_entity_media_gallery')],
                ['value' => $value],
                sprintf('value_id=%s', $id)
            );
        } catch (\Exception $e) {
            $this->logger->critical(
                self::class . "::updateMediaGalleryEntityVal: " . $e
            );
        }
    }

    /**
     * Set media attribute value
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string|string[] $mediaAttribute
     * @param string $value
     */
    public function setMediaAttribute(\Magento\Catalog\Model\Product $product, $mediaAttribute, $value)
    {
        $mediaAttributeCodes = $this->mediaConfig->getMediaAttributeCodes();

        if (is_array($mediaAttribute)) {
            foreach ($mediaAttribute as $attribute) {
                if (in_array($attribute, $mediaAttributeCodes)) {
                    $product->setData($attribute, $value);
                }
            }
        } elseif (in_array($mediaAttribute, $mediaAttributeCodes)) {
            $product->setData($mediaAttribute, $value);
        }
    }

    /**
     * Return media_gallery attribute
     *
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttribute()
    {
        if (!$this->galleryAttribute) {
            $this->galleryAttribute = $this->attributeRepository->get('media_gallery');
        }

        return $this->galleryAttribute;
    }

    /**
     * Get brand digital assets path
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return false|string
     */
    public function getBrandDirectoryPath($product)
    {
        try {
            $categoryIds = $product->getCategoryIds();
            if (!$categoryIds) {
                return false;
            }

            $digitalBrand = null;
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryRepository->get($categoryId);
                if (preg_match("/digital\sassets/i", $category->getName() . "")) {
                    $digitalBrand = $category;
                    break;
                }
            }

            if ($digitalBrand) {
                $brandName = $this->getBrandName($digitalBrand);
                if ($brandName) {
                    return DIRECTORY_SEPARATOR . $brandName . DIRECTORY_SEPARATOR . static::DIGITAL_ASSETS_FOLDER_NAME;
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * Get brand name of cateogry
     *
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return string|false
     */
    public function getBrandName($category)
    {
        if (!$category) {
            return false;
        }

        if ($category->getLevel() === static::BRAND_CATEGORY_LV) {
            return $category->getName();
        }

        if (!$category->getParentId()) {
            return false;
        }

        return $this->getBrandName($category->getParentCategory());
    }

    /**
     * Move file to brand directory
     *
     * @param string $basePath
     * @param string $brandPath
     * @param string $file
     * @param bool $toTmp
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function moveToBrandDir(string $basePath, string $brandPath, string $file, bool $toTmp = false): string
    {
        if (strrpos($file, '.tmp') == strlen($file) - 4) {
            $file = substr($file, 0, strlen($file) - 4);
        }

        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $pathInfo = pathinfo($file);

        if (!isset($pathInfo['basename'])) {
            throw new \Magento\Framework\Exception\FileSystemException(__("File not exist!"));
        }

        if ($toTmp) {
            $brandPath = "/tmp" . $brandPath;
        }

        // Get brand path destination
        $destFile = $brandPath . '/' . \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->getFilePath(
                $this->mediaDirectory->getAbsolutePath($basePath . $brandPath),
                $pathInfo['basename']
            )
        );

        // move file from default to brand path
        $this->mediaDirectory->renameFile(
            $this->getFilePath($basePath, $file),
            $this->getFilePath($basePath, $destFile)
        );

        return str_replace(
            '\\',
            '/',
            $destFile
        );
    }

    /**
     * Return full path to file
     *
     * @param string $path
     * @param string $file
     * @return string
     */
    public function getFilePath(string $path, string $file): string
    {
        $path = rtrim($path, '/');
        $file = ltrim($file, '/');

        return $path . '/' . $file;
    }
}
