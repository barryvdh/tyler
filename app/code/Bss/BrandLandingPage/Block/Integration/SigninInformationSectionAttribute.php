<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Block\Integration;

/**
 * Class SigninInformationSectionAttribute
 * No display sign up sources field in register form
 */
class SigninInformationSectionAttribute extends \Bss\B2bRegistration\Block\Integration\SigninInformationSectionAttribute
{
    /**
     * No display sign up sources field in register form
     *
     * @param int|string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isShowIn($attributeCode)
    {
        if ($attributeCode == "signup_sources") {
            return false;
        }

        return parent::isShowIn($attributeCode);
    }
}
