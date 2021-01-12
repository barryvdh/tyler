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
 * @category   BSS
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\AddressInterface;

class PaymentInformationManagement
{

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;
    /**
     * @var Json
     */
    private $json;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param Json $json
     */
    public function __construct(
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        Json $json
    ) {
        $this->addressRepository = $addressRepository;
        $this->cartRepository = $cartRepository;
        $this->helper = $helper;
        $this->json = $json;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param $result
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param AddressInterface|null $billingAddress
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $result,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        if ($this->helper->isEnable()) {
            $quoteRepository = $this->cartRepository;
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $quoteRepository->getActive($cartId);
            if($billingAddress) {
            $customerAddId = $billingAddress->getCustomerAddressId();
                if ($customerAddId) {
                    try {
                        $customAttribute = $this->addressRepository
                            ->getById($customerAddId)->getCustomAttributes();
                    } catch (LocalizedException $e) {
                        throw $e;
                    }
                    if (!empty($customAttribute)) {
                        $customAddress = [];
                        foreach ($customAttribute as $key => $attribute) {
                            if ($this->helper->isVisible($attribute->getAttributeCode())) {
                                $customAddress[$key] = $attribute->getValue();
                            }
                        }
                        $quote->getBillingAddress()->addData($customAddress);
                        if (!$quote->isVirtual()) {
                            $this->setCustomShippingAddress($quote);
                        }
                        $quote->setDataChanges(true);
                    }
                } elseif ($billingAddress) {
                    $customerAddressField = $billingAddress->getExtensionAttributes()->getCustomField();
                    if ($customerAddressField !== null) {
                        $quote->getBillingAddress()->setData('customer_address_attribute', $customerAddressField);
                        $quote->setDataChanges(true);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $quote
     * @return void
     * @throws LocalizedException
     */
    public function setCustomShippingAddress($quote)
    {
        $customAttributeForShipping = $this->addressRepository
            ->getById($quote->getShippingAddress()->getCustomerAddressId())->getCustomAttributes();
        foreach ($customAttributeForShipping as $key => $attribute) {
            if ($this->helper->isVisible($attribute->getAttributeCode())
            ) {
                $customShippingAddress[$key] = $attribute->getValue();
            }
        }
        if (!empty($customShippingAddress)) {
            $quote->getShippingAddress()->addData($customShippingAddress);
        }
    }
}
