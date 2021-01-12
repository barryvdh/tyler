<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model\Metadata;

class Form
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $customerAttribute;

    /**
     * Eav attribute factory
     * @var \Magento\Eav\Model\Config
     */
    protected $eavAttribute;
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    private $formFactory;

    /**
     * Form constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory
    ) {
        $this->request = $request;
        $this->customerAttribute = $customerattribute;
        $this->eavAttribute = $eavAttributeFactory;
        $this->formFactory = $formFactory;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllowedAttributes($subject, $result)
    {
        $page = $this->request->getFullActionName();
        $attributeDefault = ['firstname', 'lastname', 'email','password','taxvat'];
        if ($page == 'customer_account_editPost'
            && $this->customerAttribute->getConfig('bss_customer_attribute/general/enable')
        ) {
            foreach ($result as $attributeCode => $attribute) {
                if (in_array($attributeCode, $attributeDefault)) {
                    continue;
                }
                $attribute = $this->eavAttribute->create()
                    ->getAttribute('customer', $attributeCode);
                $usedInForms = $attribute->getUsedInForms();
                if (!in_array('customer_account_edit_frontend', $usedInForms)) {
                    unset($result[$attributeCode]);
                }
            }
        }
        return $result;
    }

    /**
     * @param mixed $subject
     * @param array $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAttributes($subject, $result)
    {
        $page = $this->request->getFullActionName();
        if ($page == 'sales_order_address' ||
            $page == 'sales_order_create_index' ||
            $page == 'sales_order_create_loadBlock'
        ) {
            foreach ($result as $attribute) {
                if (!$attribute->isUserDefined()) {
                    continue;
                }
                if (!$this->customerAttribute->isCustomAddressAttribute($attribute->getAttributeCode())) {
                    continue;
                }
                if ($attribute->getFrontendInput() == 'file') {
                    unset($result[$attribute->getAttributeCode()]);
                    continue;
                }
                if (!$this->customerAttribute->isEnable() ||
                    !$this->customerAttribute->isVisible($attribute->getAttributeCode())
                ) {
                    unset($result[$attribute->getAttributeCode()]);
                    continue;
                }
                if ($this->customerAttribute->isCustomAddressAttribute($attribute->getAttributeCode())) {
                    $inputType = $attribute->getFrontendInput();
                    if ($inputType == 'checkboxs') {
                        $attribute->setFrontendInput('multiselect');
                    }
                    if ($inputType == 'radio') {
                        $attribute->setFrontendInput('select');
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Unset attribute datetime
     *
     * @param $subject
     * @param $data
     * @return array attribute values
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeValidateData($subject, $data)
    {
        if ($this->customerAttribute->getConfig('bss_customer_attribute/general/enable')) {
            $attributeDefault = ['firstname', 'lastname', 'email','password','taxvat'];
            foreach ($data as $attributeCode => $attribute) {
                if (in_array($attributeCode, $attributeDefault)) {
                    continue;
                }
                if (!$this->customerAttribute->isCustomAddressAttribute($attributeCode)) {
                    continue;
                }
                $address = $this->eavAttribute->create()
                    ->getAttribute('customer_address', $attributeCode);
                if ($address->getBackendType()=="datetime") {
                    unset($data[$attributeCode]);
                }
            }
        }
        return [$data];
    }

    /**
     * Compact data array to form attribute values
     *
     * @param $subject
     * @param $result
     * @return array attribute values
     */
    public function afterCompactData($subject, $result)
    {
        /** @var \Magento\Customer\Model\Metadata\Form $addressForm */
        $addressForm = $this->formFactory->create(
            'customer_address',
            'customer_address_edit'
        );

        $customAttribute = $addressForm->getUserAttributes();
        foreach ($customAttribute as $key => $item) {
            if (!$item->isVisible() || !$this->customerAttribute->isEnable()) {
                unset($result[$key]);
            }
        }
        return $result;
    }

    /**
     * Compact data array to form attribute values
     *
     * @param $subject
     * @param callable $proceed
     * @param $data
     * @return array attribute values
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundCompactData($subject, callable $proceed, $data)
    {
        $page = $this->request->getFullActionName();
        if ($page == 'customer_address_save'
            && $this->customerAttribute->getConfig('bss_customer_attribute/general/enable')
        ) {
            $fileExist = [];
            foreach ($data as $key => $item) {
                if (!$this->customerAttribute->isCustomAddressAttribute($key)) {
                    continue;
                }
                if (is_array($item)) {
                    if (array_key_exists('file', $item) && !array_key_exists('tmp_name', $item)) {
                        $data[$key] = $item['file'];
                        $fileExist[$key] = $item['file'];
                    }
                }
            }
            $result = $proceed($data);
            if (!empty($fileExist)) {
                foreach ($fileExist as $code => $value) {
                    $result[$code] = $value;
                }
            }
            return $result;
        }
        return $proceed($data);
    }
}
