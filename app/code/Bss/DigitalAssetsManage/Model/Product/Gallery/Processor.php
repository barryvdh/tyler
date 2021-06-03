<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Model\Product\Gallery;

use Bss\DigitalAssetsManage\Helper\GetBrandDirectory;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class Processor extends \Magento\Catalog\Model\Product\Gallery\Processor
{
    /**
     * @var \Magento\Framework\File\Mime|null
     */
    protected $mime;

    /**
     * @var GetBrandDirectory
     */
    protected $getBrandDirectory;

    /**
     * Processor constructor.
     *
     * @param GetBrandDirectory $getBrandDirectory
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     * @param \Magento\Framework\File\Mime|null $mime
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        GetBrandDirectory $getBrandDirectory,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel,
        \Magento\Framework\File\Mime $mime = null
    ) {
        $this->getBrandDirectory = $getBrandDirectory;
        $this->mime = $mime ?: ObjectManager::getInstance()->get(\Magento\Framework\File\Mime::class);

        parent::__construct($attributeRepository, $fileStorageDb, $mediaConfig, $filesystem, $resourceModel, $mime);
    }

    /**
     * Add new img to gallery image, custom for brand directory
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $file
     * @param array|null $mediaAttribute
     * @param bool $move
     * @param bool $exclude
     * @param array $customEntryOptions - old entry options
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addImage(
        \Magento\Catalog\Model\Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true,
        array $customEntryOptions = []
    ) {
        // return parent::addImage($product, $file, $mediaAttribute, $move, $exclude);
        $brandDir = $this->getBrandDirectory->execute($product);

        if ($brandDir === false || trim($brandDir) === "") {
            return parent::addImage($product, $file, $mediaAttribute, $move, $exclude);
        }

        $file = $this->mediaDirectory->getRelativePath($file);
        if (!$this->mediaDirectory->isFile($file)) {
            throw new LocalizedException(__("The image doesn't exist."));
        }

        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $pathinfo = pathinfo($file);
        $imgExtensions = ['jpg', 'jpeg', 'gif', 'png'];
        if (!isset($pathinfo['extension']) || !in_array(strtolower($pathinfo['extension']), $imgExtensions)) {
            throw new LocalizedException(
                __('The image type for the file is invalid. Enter the correct image type and try again.')
            );
        }

        $fileName = \Magento\MediaStorage\Model\File\Uploader::getCorrectFileName($pathinfo['basename']);

        // Use brand path instead of dispersion
        $dispersionPath = $brandDir;
        $fileName = $dispersionPath . DS . $fileName;

        $fileName = $this->getNotDuplicatedFilename($fileName, $dispersionPath);

        $destinationFile = $this->mediaConfig->getTmpMediaPath($fileName);

        try {
            $storageHelper = $this->fileStorageDb;
            if ($move) {
                $this->mediaDirectory->renameFile($file, $destinationFile);

                //If this is used, filesystem should be configured properly
            } else {
                $this->mediaDirectory->copyFile($file, $destinationFile);

            }
            $storageHelper->saveFile($this->mediaConfig->getTmpMediaShortUrl($fileName));
        } catch (\Exception $e) {
            throw new LocalizedException(__('The "%1" file couldn\'t be moved.', $e->getMessage()));
        }

        $fileName = str_replace('\\', '/', $fileName);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $product->getData($attrCode);

        $galleryEntryData = $customEntryOptions;

        $absoluteFilePath = $this->mediaDirectory->getAbsolutePath($destinationFile);
        $imageMimeType = $this->mime->getMimeType($absoluteFilePath);
        $imageContent = $this->mediaDirectory->readFile($absoluteFilePath);
        $imageBase64 = base64_encode($imageContent);
        $imageName = $pathinfo['filename'];

        if (!is_array($mediaGalleryData)) {
            $mediaGalleryData = ['images' => []];
        }

        $position = $this->getEntryPosition($mediaGalleryData, $galleryEntryData);

        $mediaGalleryData['images'][] = [
            'file' => $fileName,
            'position' => $position,
            'label' => $galleryEntryData['label'] ?? '',
            'disabled' => (int)$exclude,
            'media_type' => 'image',
            'types' => $mediaAttribute,
            'content' => [
                'data' => [
                    ImageContentInterface::NAME => $imageName,
                    ImageContentInterface::BASE64_ENCODED_DATA => $imageBase64,
                    ImageContentInterface::TYPE => $imageMimeType,
                ]
            ]
        ];

        $product->setData($attrCode, $mediaGalleryData);

        if ($mediaAttribute !== null) {
            $this->setMediaAttribute($product, $mediaAttribute, $fileName);
        }

        return $fileName;
    }

    /**
     * Get gallery entry data
     *
     * @param array $mediaGalleryData
     * @param array $galleryEntryData
     * @return int
     */
    private function getEntryPosition(
        array &$mediaGalleryData,
        array $galleryEntryData
    ): int {
        $position = $galleryEntryData['position'] ?? 0;

        if (!isset($galleryEntryData['position'])) {
            foreach ($mediaGalleryData['images'] as &$image) {
                if (isset($image['position']) && $image['position'] > $position) {
                    $position = $image['position'];
                }
            }

            $position++;
        }

        return (int) $position;
    }
}
