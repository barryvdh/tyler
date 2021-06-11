<?php
declare(strict_types=1);
namespace Bss\HideProductField\Plugin\Block\Adminhtml\Product\Form\Gallery;

use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Gallery\Content as BePlugged;

/**
 * Class Content
 * Hide 'Add view' button for brand manager
 */
class Content
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\AggregateCustomize\Helper\Data
     */
    protected $aggregateCustomizeHelper;

    /**
     * @var Data
     */
    protected $moduleHelper;

    /**
     * Content constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper
     * @param Data $moduleHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper,
        Data $moduleHelper
    ) {
        $this->logger = $logger;
        $this->aggregateCustomizeHelper = $aggregateCustomizeHelper;
        $this->moduleHelper = $moduleHelper;
    }

    /**
     * Set custom block template and set is brand manager variable
     *
     * @param BePlugged $subject
     * @param string $template
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetTemplate(
        BePlugged $subject,
        $template
    ) {
        try {
            $isEnable = $this->moduleHelper->isEnable();
            if ($this->aggregateCustomizeHelper->isBrandManager() && $isEnable) {
                return "Bss_HideProductField::helper/gallery.phtml";
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $template;
    }

    /**
     * Hide media attributes follow the store configs
     *
     * @param BePlugged $subject
     * @param array|null $attributes
     * @return array
     */
    public function afterGetMediaAttributes(
        BePlugged $subject,
        ?array $attributes
    ): array {
        if (!$attributes) {
            return [];
        }

        $isEnable = $this->moduleHelper->isEnable();

        if ($this->aggregateCustomizeHelper->isBrandManager() && $isEnable) {
            $hideAttributes = $this->moduleHelper->getHideMediaAttributes();

            if ($hideAttributes) {
                foreach ($attributes as $code => $attribute) {
                    if (in_array($attribute->getId(), $hideAttributes)) {
                        unset($attributes[$code]);
                    }
                }
            }
        }


        return $attributes;
    }
}
