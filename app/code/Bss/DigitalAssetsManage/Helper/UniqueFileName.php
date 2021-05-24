<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\MediaStorage\Model\File\Uploader as FileUploader;

/**
 * Class UniqueFileName
 * Get custom digital assets file path
 */
class UniqueFileName
{
    const DIGITAL_ASSETS_FOLDER_NAME = "Digital Assets";
    const BRAND_CATEGORY_LV = '3';

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
     * UniqueFileName constructor.
     *
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->fileStorageDb = $fileStorageDb;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->mediaConfig = $mediaConfig;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
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
}
