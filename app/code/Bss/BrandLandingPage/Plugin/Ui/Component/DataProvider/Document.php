<?php
declare(strict_types=1);
namespace Bss\BrandLandingPage\Plugin\Ui\Component\DataProvider;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Ui\Component\DataProvider\Document as BePlugged;

/**
 * Class Document
 * signup_sources
 */
class Document
{
    /**
     * @var CustomerMetadataInterface
     */
    protected $customerMetadata;

    /**
     * Document constructor.
     *
     * @param CustomerMetadataInterface $customerMetadata
     */
    public function __construct(
        CustomerMetadataInterface $customerMetadata
    ) {
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * Set is compnay account attribute value
     *
     * @param BePlugged $subject
     * @param callable $proceed
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface
     */
    public function aroundGetCustomAttribute(
        BePlugged $subject,
        callable $proceed,
        $attributeCode
    ) {
        if ($attributeCode === "signup_sources") {
            $this->setSignupSourcesValue($subject, $attributeCode);
        }

        return $proceed($attributeCode);
    }

    /**
     * Set signup_sources attribute label instead value
     *
     * @param BePlugged $subject
     * @param string $attributeCode
     */
    protected function setSignupSourcesValue(BePlugged $subject, string $attributeCode)
    {
        $value = $subject->getData($attributeCode);

        if ($value === "0") {
            $subject->setCustomAttribute($attributeCode, __("B2B Registration Form"));
            return;
        }

        if (!$value) {
            $subject->setCustomAttribute($attributeCode, null);
            return;
        }


        try {
            $attributeMetadata = $this->customerMetadata->getAttributeMetadata($attributeCode);

            foreach ($attributeMetadata->getOptions() as $option) {
                if ($option->getValue() == $value) {
                    $attributeOption = $option;
                }
            }
            if (!isset($attributeOption)) {
                $subject->setCustomAttribute($attributeCode, null);
                return;
            }

            $subject->setCustomAttribute($attributeCode, $attributeOption->getLabel());
        } catch (\Exception $e) {
            $subject->setCustomAttribute($attributeCode, null);
        }
    }
}
