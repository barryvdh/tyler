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
namespace Bss\CustomerAttributes\Observer\Order;

/**
 * Class OrderEmailTemplateVars
 *
 * @package Bss\CustomerAttributes\Observer\Order
 */
class ShipmentEmailTemplateVars implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\GetHtmltoEmail
     */
    protected $helper;

    /**
     * OrderEmailTemplateVars constructor.
     * @param \Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper
     */
    public function __construct(\Bss\CustomerAttributes\Helper\GetHtmltoEmail $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $transport = $observer->getData('transportObject');
        $order = $transport['order'];
        $customerBillingAddress = $order->getBillingAddress()->getCustomerAddressAttribute();
        $customerShippingAddress = $order->getBillingAddress()->getCustomerAddressAttribute();
        if (!$order->getCustomerId()){
            $transport['bss_billing_address_attributes'] = $this->helper->getAddressVariableGuestEmailHtml(
                $customerBillingAddress
            );
            $transport['bss_shipping_address_attributes'] = $this->helper->getAddressVariableGuestEmailHtml(
                $customerShippingAddress
            );
        } else {
            $transport['bss_customer_attributes'] = $this->helper->getVariableEmailHtml($order->getCustomerId());
            $transport['bss_billing_address_attributes'] = $this->helper->getAddressVariableOrderEmailHtml(
                $customerBillingAddress,
                $order->getCustomerId()
            );
            $transport['bss_shipping_address_attributes'] = $this->helper->getAddressVariableOrderEmailHtml(
                $customerShippingAddress,
                $order->getCustomerId()
            );
        }
    }
}
