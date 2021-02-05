<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Bss\OrderRestriction\Model\ResourceModel\BundleProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

/**
 * Class ObjectHelper
 * Get class object
 */
class ObjectHelper
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var BundleProduct
     */
    private $bundleProductResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * ObjectHelper constructor.
     *
     * @param ConfigProvider $configProvider
     * @param BundleProduct $bundleProductResource
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableType
     */
    public function __construct(
        ConfigProvider $configProvider,
        BundleProduct $bundleProductResource,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType
    ) {
        $this->configProvider = $configProvider;
        $this->bundleProductResource = $bundleProductResource;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
    }

    /**
     * Get bundle product resource object
     *
     * @return BundleProduct
     */
    public function getBundleProductResource()
    {
        return $this->bundleProductResource;
    }

    /**
     * Get config provider
     *
     * @return ConfigProvider
     */
    public function getConfigProvider()
    {
        return $this->configProvider;
    }

    /**
     * Get product repository
     *
     * @return ProductRepositoryInterface
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * Get configurable type
     *
     * @return Configurable
     */
    public function getConfigurableProductType()
    {
        return $this->configurableType;
    }
}
