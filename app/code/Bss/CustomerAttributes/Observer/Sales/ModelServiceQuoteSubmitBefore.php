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

namespace Bss\CustomerAttributes\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Serialize\Serializer\Json;

class ModelServiceQuoteSubmitBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var Json
     */
    private $json;

    /**
     * PaymentInformationManagement constructor.
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param Json $json
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        Json $json
    ) {
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
        $this->json = $json;
    }
    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return ModelServiceQuoteSubmitBefore
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        try {
            if ($quote->getBillingAddress()) {
                $billingAddressAttributes = $quote->getBillingAddress()->getDataModel()->getCustomAttributes();
                $addressAttributesArrays = [];
                if (!empty($billingAddressAttributes)) {
                    foreach ($billingAddressAttributes as $item) {
                        if ($item->getValue() !=='' &&
                            $this->helper->isAddressShowInOrderDetail($item->getAttributeCode())) {
                            $addressAttribute = $this->attributeRepository
                                ->get('customer_address', $item->getAttributeCode());
                            $addressValue = $this->helper->getValueAddressAttributeForOrder(
                                $addressAttribute,
                                $item->getValue()
                            );
                            $value = [
                                'value' => $addressValue,
                                'label' => $addressAttribute->getFrontendLabel()
                            ];
                            $addressAttributesArrays['billing'][$item->getAttributeCode()] = $value;
                        }
                    }
                } else {
                    $customerBillingAddressAttributes = $quote->getBillingAddress()->getCustomerAddressAttribute();
                    if (!empty($customerBillingAddressAttributes)) {
                        foreach ($this->json->unserialize($customerBillingAddressAttributes) as $item) {
                            try {
                                $attributeCode = trim($item['attribute_code'], '[]');
                                if ($item['value'] !=='' &&
                                    $this->helper->isAddressShowInOrderDetail($attributeCode)) {
                                    $addressAttribute = $this->attributeRepository
                                        ->get('customer_address', $attributeCode);
                                    $addressValue = $this->helper->getValueAddressAttributeForOrder(
                                        $addressAttribute,
                                        $item['value']
                                    );
                                    $value = [
                                        'value' => $addressValue,
                                        'label' => $addressAttribute->getFrontendLabel()
                                    ];
                                    $addressAttributesArrays['billing'][$attributeCode] = $value;
                                }
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }
                }
                if (!empty($addressAttributesArrays['billing'])) {
                    $order->getBillingAddress()
                        ->setCustomerAddressAttribute($this->json->serialize($addressAttributesArrays['billing']));
                }
            }
            if (!$quote->isVirtual()) {
                $shippingAddressAttributes = $quote->getShippingAddress()->getDataModel()->getCustomAttributes();
                if (!empty($shippingAddressAttributes)) {
                    foreach ($shippingAddressAttributes as $item) {
                        if ($item->getValue() !=='' &&
                            $this->helper->isAddressShowInOrderDetail($item->getAttributeCode())) {
                            $addressAttribute = $this->attributeRepository
                                ->get('customer_address', $item->getAttributeCode());
                            $addressValue = $this->helper->getValueAddressAttributeForOrder(
                                $addressAttribute,
                                $item->getValue()
                            );
                            $value = [
                                'value' => $addressValue,
                                'label' => $addressAttribute->getFrontendLabel()
                            ];
                            $addressAttributesArrays['shipping'][$item->getAttributeCode()] = $value;
                        }
                    }
                } else {
                    $customerShippingAddressAttribute = $quote->getShippingAddress()->getCustomerAddressAttribute();
                    if (!empty($customerShippingAddressAttribute)) {
                        $addressAttributesArrays = [];
                        foreach ($this->json->unserialize($customerShippingAddressAttribute) as $item) {
                            $attributeCode = trim($item['attribute_code'], '[]');
                            try {
                                if ($item['value'] !== '' &&
                                    $this->helper->isAddressShowInOrderDetail($attributeCode)) {
                                    $addressAttribute = $this->attributeRepository
                                        ->get('customer_address', $attributeCode);
                                    $addressValue = $this->helper->getValueAddressAttributeForOrder(
                                        $addressAttribute,
                                        $item['value']
                                    );
                                    $value = [
                                        'value' => $addressValue,
                                        'label' => $addressAttribute->getFrontendLabel()
                                    ];
                                    $addressAttributesArrays['shipping'][$attributeCode] = $value;
                                }
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }
                }
                if (!empty($addressAttributesArrays['shipping'])) {
                    $order->getShippingAddress()->setCustomerAddressAttribute($this->json->serialize($addressAttributesArrays['shipping']));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
        return $this;
    }
}
