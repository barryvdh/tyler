<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Model\Customer\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class Brand
 * Get list brand to input source
 */
class Brand extends AbstractSource
{
    /**
     * @var \Bss\BrandLandingPage\Model\Config\Source\Brand
     */
    private $brandSource;

    /**
     * Brand constructor.
     *
     * @param \Bss\BrandLandingPage\Model\Config\Source\Brand $brandSource
     */
    public function __construct(
        \Bss\BrandLandingPage\Model\Config\Source\Brand $brandSource
    ) {
        $this->brandSource = $brandSource;
    }

    /**
     * @inheritDoc
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = array_merge(
                [
                    ['label' => __("Select a Brand"), 'value' => ""],
                    ['label' => __("B2B Registration Form"), 'value' => "0"]
                ],
                $this->brandSource->toOptionArray()
            );
        }

        return $this->_options;
    }
}
