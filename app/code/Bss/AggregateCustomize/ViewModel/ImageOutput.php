<?php
declare(strict_types=1);

namespace Bss\AggregateCustomize\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\ImagesConfigFactoryInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ImageOutput
 * Get main child product image
 */
class ImageOutput implements ArgumentInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var array
     */
    protected $galleryImagesConfig;

    /**
     * @var ImagesConfigFactoryInterface
     */
    protected $galleryImagesConfigFactory;

    /**
     * @var Collection
     */
    protected $imageGalleryConfig;

    /**
     * @var UrlBuilder
     */
    protected $imageUrlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * ImageOutput constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ImagesConfigFactoryInterface $galleryImagesConfigFactory
     * @param UrlBuilder $urlBuilder
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $galleryImagesConfig
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ImagesConfigFactoryInterface $galleryImagesConfigFactory,
        UrlBuilder $urlBuilder,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $galleryImagesConfig = []
    ) {
        $this->productRepository = $productRepository;
        $this->galleryImagesConfig = $galleryImagesConfig;
        $this->galleryImagesConfigFactory = $galleryImagesConfigFactory;
        $this->imageUrlBuilder = $urlBuilder;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Get product
     *
     * @param int $productId
     * @return false|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function getProduct($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get product main image data
     *
     * @param int $productId
     * @return \Magento\Framework\DataObject
     */
    public function getProductImage($productId)
    {
        $defaultImg = new \Magento\Framework\DataObject(
            [
                'small_image_url' => $this->imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'large_image_url' => $this->imageHelper->getDefaultPlaceholderUrl('image')
            ]
        );
        if (!$productId) {
            return $defaultImg;
        }

        $product = $this->getProduct($productId);
        $images = $product->getMediaGalleryImages();
        if (!$images instanceof \Magento\Framework\Data\Collection) {
            return $defaultImg;
        }

        foreach ($images as $image) {
            $galleryImagesConfig = $this->getGalleryImagesConfig()->getItems();
            foreach ($galleryImagesConfig as $imageConfig) {
                $image->setData(
                    $imageConfig->getData('data_object_key'),
                    $this->imageUrlBuilder->getUrl($image->getFile(), $imageConfig['image_id'])
                );
            }
        }
        foreach ($images as $image) {
            // get is main img
            if ($product->getImage() == $image->getFile()) {
                return $image;
            }
        }

        return $defaultImg;
    }

    /**
     * Returns image gallery config object
     *
     * @return Collection
     */
    private function getGalleryImagesConfig()
    {
        if (!$this->imageGalleryConfig) {
            $galleryImageConfig = $this->galleryImagesConfigFactory->create($this->galleryImagesConfig);
            $this->imageGalleryConfig = $galleryImageConfig;
        }

        return $this->imageGalleryConfig;
    }
}
