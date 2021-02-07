<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Bss\OrderRestriction\Model\ResourceModel\BundleProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ObjectHelper
 * Get class object
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ObjectHelper constructor.
     *
     * @param ConfigProvider $configProvider
     * @param BundleProduct $bundleProductResource
     * @param ProductRepositoryInterface $productRepository
     * @param Configurable $configurableType
     * @param CheckoutSession $checkoutSession
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigProvider $configProvider,
        BundleProduct $bundleProductResource,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        CheckoutSession $checkoutSession,
        SerializerInterface $serializer
    ) {
        $this->configProvider = $configProvider;
        $this->bundleProductResource = $bundleProductResource;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get checkout session object
     *
     * @return CheckoutSession
     */
    public function getCheckoutSession()
    {
        return $this->checkoutSession;
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
