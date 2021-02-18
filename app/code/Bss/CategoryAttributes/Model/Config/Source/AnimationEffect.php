<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Model\Config\Source;

/**
 * Class AnimationEffect for each transition type
 */
class AnimationEffect implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'none', 'label' => __('None')],
            ['value' => 'elastic', 'label' => __('Elastic')],
            ['value' => 'fade', 'label' => __('Fade')],
        ];
    }
}
