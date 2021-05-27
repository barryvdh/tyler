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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Api;

/**
 * Class OrderRepositoryInterface
 *
 * @package Bss\CustomerAttributes\Plugin\Api
 */
class OrderRepositoryInterface
{
    /**
     * @var \Bss\CustomerAttributes\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * OrderRepositoryInterface constructor.
     * @param \Bss\CustomerAttributes\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Bss\CustomerAttributes\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->helper = $helper;
        $this->json = $json;
        $this->order = $order;
    }

    /**
     * Add extension attribute into order API
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param string $id
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet($subject, \Magento\Sales\Api\Data\OrderInterface $order, $id)
    {
        if ($this->helper->isEnable()) {
            $order = $order->load($id);
            if ($order->getCustomerAttribute()) {
                $customerAttr = $this->json->unserialize($order->getCustomerAttribute());
                $extensionAttributes = $order->getExtensionAttributes();
                if ($extensionAttributes) {
                    $extensionAttributes->setCustomerAttribute($customerAttr);
                    $order->setExtensionAttributes($extensionAttributes);
                }
            }
        }
        return $order;
    }

    /**
     * Add extension attribute into orders API
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $result
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        $subject,
        \Magento\Sales\Api\Data\OrderSearchResultInterface $result,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ) {
        if ($this->helper->isEnable()) {
            $items = $result->getItems();
            foreach ($items as $item) {
                $orderId = $item->getEntityId();
                $order = $this->order->load($orderId);
                if ($order->getCustomerAttribute()) {
                    $customerAttr = $this->json->unserialize($order->getCustomerAttribute());
                    $extensionAttributes = $item->getExtensionAttributes();
                    if ($extensionAttributes) {
                        $extensionAttributes->setCustomerAttribute($customerAttr);
                        $item->setExtensionAttributes($extensionAttributes);
                    }
                }
            }
        }
        return $result;
    }
}
