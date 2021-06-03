<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Model;

use Bss\DigitalAssetsManage\Helper\GetBrandDirectory;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\FileSystemException;
use Exception;
use Magento\MediaStorage\Model\File\Uploader;
use \Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use \Magento\Framework\File\Mime;
use Magento\Framework\Api\Data\ImageContentInterfaceFactory;

/**
 * Class ProductDigitalAssetsProcessor
 * Process move origin file to brand digital assets folder
 */
class ProductDigitalAssetsProcessor
{
    /**
     * @var GetBrandDirectory
     */
    protected $getBrandDirectory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var MediaConfig
     */
    protected $mediaConfig;

    /**
     * @var WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var Mime
     */
    protected $mime;

    /**
     * @var ImageContentInterfaceFactory
     */
    protected $imageContentInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductDigitalAssetsProcessor constructor.
     *
     * @param GetBrandDirectory $getBrandDirectory
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param Filesystem $filesystem
     * @param MediaConfig $mediaConfig
     * @param Mime $mime
     * @param ImageContentInterfaceFactory $imageContentInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @throws FileSystemException
     */
    public function __construct(
        GetBrandDirectory $getBrandDirectory,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        Filesystem $filesystem,
        MediaConfig $mediaConfig,
        Mime $mime,
        ImageContentInterfaceFactory $imageContentInterfaceFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->getBrandDirectory = $getBrandDirectory;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;

        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
        $this->mime = $mime;
        $this->imageContentInterfaceFactory = $imageContentInterfaceFactory;
        $this->productRepository = $productRepository;
    }

    /**
     * Process move origin file to brand digital assets folder
     *
     * @param Product $product
     * @param string|null $brandDir
     */
    public function process(
        Product $product,
        string $brandDir = null
    ) {
        try {
            if ($this->removeAssetsFromBrandFolder($product)) {
                return;
            }
        } catch (Exception $e) {
            $this->logger->critical(
                "BSS - ERROR: When remove asssets from brand folder. Detail: " . $e
            );
        }

        if ($brandDir === null) {
            $brandDir = $this->getBrandDirectory->execute($product);
        }

        if (!$brandDir) {
            return;
        }

        $this->moveAssetsToBrandFolder($product, $brandDir);
    }

    /**
     * Move product asssets to brand dirctory
     *
     * @param Product|int $product
     * @param string $brandPath
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function moveAssetsToBrandFolder($product, string $brandPath)
    {
        if (is_int($product)) {
            try {
                $product = $this->productRepository->getById($product);
            } catch (Exception $e) {
                $this->logger->critical(__("BSS - ERROR: Can't get product: ") . $e->getMessage());
                return;
            }
        }
        $existingMediaGalleryEntries = $this->getMediaGalleryEntries($product);

        $entryChanged = false;
        foreach ($existingMediaGalleryEntries as $entry) {
            try {
                if (strpos($entry->getFile(), ltrim($brandPath)) !== false) {
                    continue;
                }

                $this->validateOriginalEntryPath($entry);

                $this->moveFileInCatalogProductFolder(
                    $brandPath,
                    $entry,
                    $product,
                    $entryChanged
                );

                // phpcs:disable Magento2.Functions.DiscouragedFunction
//                $pathInfo = pathinfo($imgPath);
//                $destinationFile = $this->getFilePath($this->mediaConfig->getBaseMediaPath(), $imgPath);
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
//
//                $entryChanged = true;
            } catch (FileSystemException $e) {
                $this->logger->critical(__("BSS - ERROR: Can't move the assets file because: ") . $e->getMessage());
            } catch (Exception $e) {
                $this->logger->critical(
                    "BSS - ERROR: " . $e
                );
            }
        }

        try {
            if ($entryChanged) {
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                $this->productRepository->save($product);
//                $this->cleanBrandTmpFolder($brandDir);
            }
        } catch (Exception $e) {
            $this->logger->critical(
                "BSS - ERROR: When update product gallery. Detail: " . $e
            );
        }
    }

    /**
     * Move file in catalog category folder
     *
     * @param string $subPath
     * @param ProductAttributeMediaGalleryEntryInterface $entry
     * @param Product $product
     * @param bool $entryChanged
     * @throws FileSystemException
     */
    protected function moveFileInCatalogProductFolder(
        string $subPath,
        ProductAttributeMediaGalleryEntryInterface $entry,
        Product $product,
        bool &$entryChanged
    ) {
        $imgPath = $this->moveToBrandDir(
            $this->mediaConfig->getBaseMediaPath(),
            $subPath,
            $entry->getFile()
        );
        $this->updateMediaGalleryEntityVal((int) $entry->getId(), $imgPath);
        if ($entry->getTypes() !== null || $entry->getTypes()) {
            $this->setMediaAttribute($product, $entry->getTypes(), $imgPath);

            // Set file for entry to escapse magento save product will execute event resize origin file
            $entry->setFile($imgPath);
            $entryChanged = true;
        }
    }

    /**
     * Move assets to other folder if product not in digital assets category
     *
     * @param Product|int $product
     * @param string|null $brandPath
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function removeAssetsFromBrandFolder($product, string $brandPath = null): bool
    {
        if (is_int($product)) {
            $product = $this->productRepository->getById($product);
        }
        $needProcess = true;
        if (!$brandPath) {
            $newBrandDir = $this->getBrandDirectory->execute($product);
            $oldBrandDir = $this->getBrandDirectory->execute($product, true);
            $brandPath = $oldBrandDir;
            $needProcess = $oldBrandDir !== false && $newBrandDir === false;
        }
        if ($needProcess) {
            $existingMediaGalleryEntries = $this->getMediaGalleryEntries($product);

            $entryChanged = false;
            foreach ($existingMediaGalleryEntries as $entry) {
                if (strpos($entry->getFile(), ltrim($brandPath)) !== false) {
                    // phpcs:disable Magento2.Functions.DiscouragedFunction
                    $pathinfo = pathinfo($entry->getFile());
                    $fileName = $pathinfo['basename'];
                    $dispersionPath = Uploader::getDispersionPath($fileName);
                    $dispersionPath = ltrim($dispersionPath, DS);
                    $dispersionPath = rtrim($dispersionPath, DS);

                    $this->moveFileInCatalogProductFolder(
                        DS . $dispersionPath . DS,
                        $entry,
                        $product,
                        $entryChanged
                    );
                }
            }

            if ($entryChanged) {
                $product->setMediaGalleryEntries($existingMediaGalleryEntries);

                $this->productRepository->save($product);
            }

            return true;
        }

        return false;
    }

    /**
     * Get media gallery entries
     *
     * @param Product $product
     * @return array|\Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterface[]
     */
    private function getMediaGalleryEntries(Product $product)
    {
        try {
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        } catch (Exception $e) {
            $existingMediaGalleryEntries = [];
            $this->logger->critical(
                "BSS - ERROR: Can't not get the media galleries. Detail: " .
                $e
            );
        }

        return $existingMediaGalleryEntries;
    }

    /**
     * Clean the tmp folder
     *
     * @param string $brandDir
     */
    protected function cleanBrandTmpFolder(string $brandDir): void
    {
        try {
            $this->mediaDirectory->delete(
                $this->getFilePath(
                    $this->mediaConfig->getBaseMediaPath(),
                    DS . $this->getFilePath(
                        'tmp',
                        $brandDir
                    )
                )
            );
        } catch (Exception $e) {
            $this->logger->critical(__("Can't clean the %1 tmp folder. Detail: %2", $brandDir, $e));
        }
    }

    /**
     * Set media attribute value
     *
     * @param Product $product
     * @param string|string[] $mediaAttribute
     * @param string $value
     */
    public function setMediaAttribute(Product $product, $mediaAttribute, $value)
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
     * Validate original file
     *
     * @param Product\Gallery\Entry $entry
     * @throws FileSystemException
     */
    public function validateOriginalEntryPath(Product\Gallery\Entry $entry): void
    {
        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath(
            $this->getFilePath(
                $this->mediaConfig->getBaseMediaPath(),
                $entry->getFile()
            )
        );

        // phpcs:disable Magento2.Functions.DiscouragedFunction
        if (!file_exists($absoluteFilePath)) {
            throw new FileSystemException(__("File %1 not found!", $absoluteFilePath));
        }
    }

    /**
     * Move file from origin path to brand digital assets folder
     *
     * @param string $basePath
     * @param string $brandPath
     * @param string $file
     * @param bool $toTmp
     * @return string
     * @throws FileSystemException
     */
    protected function moveToBrandDir(string $basePath, string $brandPath, string $file, bool $toTmp = false): string
    {
        if ($toTmp) {
            $brandPath = DS . $this->getFilePath(
                'tmp',
                $brandPath
            );
        }
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $pathInfo = pathinfo($file);

        if (!isset($pathInfo['basename'])) {
            throw new FileSystemException(__("File not exist!"));
        }

        // Get final brand digital assets destination file path
        $destFile = $brandPath . DS . $this->getUniqueFileNameInBrandDigitalFolder(
            $pathInfo['basename'],
            $basePath . $brandPath
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
     * Update gallery entry file path to Db
     *
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
        } catch (Exception $e) {
            $this->logger->critical(
                "BSS - ERROR: When update media gallery entry to DB. Detail: " . $e
            );
        }
    }

    /**
     * Get unique file name from brand digital assets folder
     *
     * @param string $fileName
     * @param string $brandPath
     * @return string
     */
    protected function getUniqueFileNameInBrandDigitalFolder(string $fileName, string $brandPath): string
    {
        return Uploader::getNewFileName(
            $this->getFilePath(
                $this->mediaDirectory->getAbsolutePath($brandPath),
                $fileName
            )
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

        return $path . DS . $file;
    }
}
