<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Block\Brand\Widget;

use Magento\Widget\Block\BlockInterface;

/**
 * Class BrandSignup
 * Render widget
 */
class BrandSignup extends \Bss\B2bRegistration\Block\CustomerRegister implements BlockInterface
{
    /**
     * Get assigned brand id
     *
     * @return int
     */
    public function getBrandCategoryId()
    {
        return $this->getData('brand');
    }
    /**
     * Get date of birth field html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDobFieldHtml()
    {
        $dobBlock = $this->getLayout()->createBlock(
            \Magento\Customer\Block\Widget\Dob::class,
            'bss.register.dob'
        )->setTemplate('Magento_Customer::widget/dob.phtml');

        return $dobBlock->toHtml();
    }

    /**
     * Get Tax VAT field html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTaxVatFieldHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\Customer\Block\Widget\Taxvat::class,
            'bss-register-taxvat'
        )->setTemplate('Magento_Customer::widget/taxvat.phtml')->toHtml();
    }

    /**
     * Get gender field html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getGenderFieldHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\Customer\Block\Widget\Gender::class,
            'bss-register-gender'
        )->setTemplate('Magento_Customer::widget/gender.phtml')->toHtml();
    }

    /**
     * Get bss captcha field html
     *
     * @return string
     */
    public function getBssCaptchaHtml()
    {
        return '';
    }

    /**
     * Get form additional information html
     *
     * @return string
     */
    public function getFormAdditionalInfoHtml()
    {
        return '';
    }

    /**
     * Get sign in information section html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSigninInformationSectionHtml()
    {
        return $this->getLayout()->createBlock(
            \Bss\BrandLandingPage\Block\Integration\SigninInformationSectionAttribute::class,
            'signin.information.section'
        )->toHtml();
    }

    /**
     * Get bss form additional information html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBssFormAdditionalInfoHtml()
    {
        return $this->getLayout()->createBlock(
            \Bss\BrandLandingPage\Block\Integration\RegisterForm::class,
            'bss.form.additional.info'
        )->toHtml();
    }

    /**
     * Get form fields before html
     *
     * @return string
     */
    public function getFormFieldsBeforeHtml()
    {
        return '';
    }

    /**
     * Get customer form extra information html
     *
     * @return string
     */
    public function getCustomerFormRegisterExtraHtml()
    {
        return '';
    }

    /**
     * Get customer form register newsletter html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerFormRegisterNewsletterHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Template::class,
            'customer.form.register.newsletter'
        )->setTemplate('Magento_Newsletter::form/register/newsletter.phtml')->toHtml();
    }

    /**
     * Get personal information section html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPersonalInformationSectionHtml()
    {
        return $this->getLayout()->createBlock(
            \Bss\BrandLandingPage\Block\Integration\PersonalInformationSectionAttribute::class,
            'personal.information.section'
        )->toHtml();
    }
}
