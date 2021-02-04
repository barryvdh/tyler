<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\Source;

use Bss\OrderRestriction\Helper\ConfigProvider;
use Magento\Framework\Data\ValueSourceInterface;

/**
 * Class OrderRuleConfiguration
 */
class OrderRuleConfiguration implements ValueSourceInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * OrderRuleConfiguration constructor.
     *
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @inheritDoc
     */
    public function getValue($name)
    {
        return $this->configProvider->getDefaultSaleQtyValue();
    }
}
