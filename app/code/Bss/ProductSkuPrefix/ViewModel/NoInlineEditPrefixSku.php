<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\ViewModel;

use Bss\ProductSkuPrefix\Helper\ConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class NoInlineEditPrefixSku implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * NoInlineEditPrefixSku constructor.
     *
     * @param ConfigProvider $configProvider
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ConfigProvider $configProvider,
        SerializerInterface $serializer
    ) {
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }

    /**
     * Get prefix sku data
     *
     * @return array
     */
    public function getPrefixData(): array
    {
        return $this->configProvider->getSerializedConfigData();
    }

    /**
     * Serialize data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data)
    {
        return $this->serializer->serialize($data);
    }
}
