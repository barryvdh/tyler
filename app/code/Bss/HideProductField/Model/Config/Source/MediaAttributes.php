<?php
declare(strict_types=1);
namespace Bss\HideProductField\Model\Config\Source;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Class MediaAttributes
 * Media options array
 */
class MediaAttributes implements OptionSourceInterface
{
    /**
     * @var ProductInterface
     */
    protected $product;

    /**
     * MediaAttributes constructor.
     *
     * @param ProductInterface $product
     */
    public function __construct(
        ProductInterface $product
    ) {
        $this->product = $product;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        $mediaAttributes = $this->getMediaAttributes();

        $options = [];
        foreach ($mediaAttributes as $attribute) {
            $options[] = [
                'label' => $attribute->getFrontend()->getLabel(),
                'value' => $attribute->getId(),
            ];
        }

        return $options;
    }

    /**
     * Get media attributes of product instance
     *
     * @return AbstractAttribute[]
     */
    public function getMediaAttributes(): array
    {
        $mediaAttributes = [];
        foreach ($this->product->getAttributes() as $attribute) {
            if ($attribute->getFrontend()->getInputType() == 'media_image') {
                $mediaAttributes[$attribute->getAttributeCode()] = $attribute;
            }
        }

        return $mediaAttributes;
    }
}
