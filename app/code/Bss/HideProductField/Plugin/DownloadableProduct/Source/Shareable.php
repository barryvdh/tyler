<?php
declare(strict_types=1);

namespace Bss\HideProductField\Plugin\DownloadableProduct\Source;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Downloadable\Model\Link;

/**
 * Class Shareable
 * Remove use config option for brand manager
 */
class Shareable
{
    /**
     * @var Data
     */
    private $aggregateCustomizeHelper;

    /**
     * Shareable constructor.
     *
     * @param Data $aggregateCustomizeHelper
     */
    public function __construct(
        Data $aggregateCustomizeHelper
    ) {
        $this->aggregateCustomizeHelper = $aggregateCustomizeHelper;
    }

    /**
     * Remove use config option for brand manager
     *
     * @param \Magento\Downloadable\Model\Source\Shareable $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(
        \Magento\Downloadable\Model\Source\Shareable $subject,
        array $result
    ) {
        if ($this->aggregateCustomizeHelper->isBrandManager()) {
            return array_filter($result, function ($item) {
                return $item['value'] != Link::LINK_SHAREABLE_CONFIG;
            });
        }

        return $result;
    }
}
