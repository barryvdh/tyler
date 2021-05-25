<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Model\Product\Gallery;

use Bss\DigitalAssetsManage\Helper\UniqueFileName;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class UpdateHandler
 * Custom save img location for brand digital assets
 */
class UpdateHandler extends \Magento\Catalog\Model\Product\Gallery\UpdateHandler
{
    protected $product;

    /**
     * @var UniqueFileName
     */
    protected $uniqueFileName;

    /**
     * UpdateHandler constructor.
     *
     * @param UniqueFileName $uniqueFileName
     * @param \Magento\Framework\EntityManager\MetadataPool $metadataPool
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param StoreManagerInterface|null $storeManager
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        UniqueFileName $uniqueFileName,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Gallery $resourceModel,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        StoreManagerInterface $storeManager = null
    ) {
        $this->uniqueFileName = $uniqueFileName;
        parent::__construct(
            $metadataPool,
            $attributeRepository,
            $resourceModel,
            $jsonHelper,
            $mediaConfig,
            $filesystem,
            $fileStorageDb,
            $storeManager
        );
    }

    /**
     * Set local product var
     *
     * @param object $product
     * @param array $arguments
     * @return object
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($product, $arguments = [])
    {
        $this->product = $product;
        return parent::execute($product, $arguments);
    }

    /**
     * Move to brand assets
     *
     * @param string $file
     * @param bool $forTmp
     * @return string
     */
    protected function getUniqueFileName($file, $forTmp = false)
    {
        return $this->uniqueFileName->get($this->product, $file, $forTmp);
    }
}
