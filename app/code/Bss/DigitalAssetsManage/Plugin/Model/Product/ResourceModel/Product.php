<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Plugin\Model\Product\ResourceModel;

use Bss\DigitalAssetsManage\Helper\GetBrandDirectory;
use Bss\DigitalAssetsManage\Model\DigitalImageProcessor;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as BePlugged;
use Magento\Framework\DataObject;

/**
 * Class Product
 */
class Product
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var DigitalImageProcessor
     */
    protected $digitalImageProcessor;

    /**
     * @var GetBrandDirectory
     */
    protected $getBrandDirectory;

    /**
     * Product constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param DigitalImageProcessor $digitalImageProcessor
     * @param GetBrandDirectory $getBrandDirectory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        DigitalImageProcessor $digitalImageProcessor,
        GetBrandDirectory $getBrandDirectory
    ) {
        $this->productRepository = $productRepository;
        $this->digitalImageProcessor = $digitalImageProcessor;
        $this->getBrandDirectory = $getBrandDirectory;
    }

    /**
     * Set the file link for delete if needed
     *
     * @param BePlugged $subject
     * @param \Magento\Catalog\Model\Product $object
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        BePlugged $subject,
        \Magento\Catalog\Model\Product $object
    ): array {
        $productId = $object->getId();
        $product = $this->productRepository->getById($productId);
        $brandPath = $this->getBrandDirectory->execute($product);
        $extension = $product->getExtensionAttributes();
        $links = $extension->getDownloadableProductLinks();
        $samples = $extension->getDownloadableProductSamples();
        $object->setNeedDeleteDownloadableProductLinks($links);
        $object->setNeedDeleteDownloadableProductSamples($samples);
        $galleries = $this->digitalImageProcessor->getMediaGalleryEntries($product);
        $object->setNeedDeleteGalleryEntries($galleries);
        $object->setBrandPath($brandPath);

        return [$object];
    }
}
