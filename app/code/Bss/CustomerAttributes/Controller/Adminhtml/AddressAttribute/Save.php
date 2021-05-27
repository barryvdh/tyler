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

namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Save
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class Save extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Save
{
    /**
     * @var \Bss\CustomerAttributes\Helper\SaveObject
     */
    protected $saveObject;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    protected $helperCustomerAttribute;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\CustomerAttributes\Helper\SaveObject $saveObject
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\CustomerAttributes\Helper\SaveObject $saveObject,
        CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerAttributes\Helper\Customerattribute $helperCustomerAttribute
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $productUrl,
            $eavEntity,
            $resultPageFactory,
            $saveObject,
            $customerRepository,
            $helperCustomerAttribute
        );
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_entityTypeId = $this->eavEntity
            ->setType('customer_address')->getTypeId();
        return \Magento\Backend\App\Action::dispatch($request);
    }

    /**
     * Save Attribute Execute
     *
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $entityType = 'customer_address';
            $this->saveData($data, $entityType);
        }
        return $this->returnResult('addressattribute/*/', [], ['error' => true]);
    }

    /**
     * Set Use In Form
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getUsedInForm($data)
    {
        $usedInForms = $this->saveObject->returnDataObjectFactory()->create();

        $usedInForms[0] = 'adminhtml_customer';
        $usedInForms[1] = 'customer_register_address';
        $usedInForms[2] = 'customer_address';
        $usedInForms[3] = 'is_customer_attribute';
        $num = 4;
        if ($this->setUseInFormAdminCheckout($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormAdminCheckout($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInFormCheckout($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormCheckout($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInAddressBook($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInAddressBook($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInFormOrderDetail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormOrderDetail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInFormAdminOrderDetail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInFormAdminOrderDetail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInOrderEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInOrderEmail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInInvoiceEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInInvoiceEmail($data, $usedInForms, $num);
            $num++;
        }
        if ($this->setUseInShippingEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInShippingEmail($data, $usedInForms, $num);
            $num++;
        }

        if ($this->setUseInMemoEmail($data, $usedInForms, $num)) {
            $usedInForms[$num] = $this->setUseInMemoEmail($data, $usedInForms, $num);
        }
        return $usedInForms;
    }

    /**
     * Set Attribute use in Admin Checkout page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormAdminCheckout($data, $usedInForms, $num)
    {
        if (isset($data['adminhtml_customer_address']) && $data['adminhtml_customer_address'] == 1) {
            $usedInForms[$num] = 'adminhtml_customer_address';
            return $usedInForms[$num];
        }
        return false;
    }
    /**
     * Set Attribute use in Checkout Page page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormCheckout($data, $usedInForms, $num)
    {
        if (isset($data['show_checkout_frontend']) && $data['show_checkout_frontend'] == 1) {
            $usedInForms[$num] = 'show_checkout_frontend';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Address Book page
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */

    private function setUseInAddressBook($data, $usedInForms, $num)
    {
        if (isset($data['customer_address_edit']) && $data['customer_address_edit'] == 1) {
            $usedInForms[$num] = 'customer_address_edit';
            return $usedInForms[$num];
        }
        return false;
    }
    /**
     * Set Attribute use in Order Details
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormOrderDetail($data, $usedInForms, $num)
    {
        if (isset($data['order_detail']) && $data['order_detail'] == 1) {
            $usedInForms[$num] = 'order_detail';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Admin Order Details
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInFormAdminOrderDetail($data, $usedInForms, $num)
    {
        if (isset($data['adminhtml_order_detail']) && $data['adminhtml_order_detail'] == 1) {
            $usedInForms[$num] = 'adminhtml_order_detail';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Order email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInOrderEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_order_email']) && $data['show_in_order_email'] == 1) {
            $usedInForms[$num] = 'show_in_order_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Invoice Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInInvoiceEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_invoice_email']) && $data['show_in_invoice_email'] == 1) {
            $usedInForms[$num] = 'show_in_invoice_email';
            return $usedInForms[$num];
        }
        return false;
    }

    /**
     * Set Attribute use in Invoice Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInShippingEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_shipping_email']) && $data['show_in_shipping_email'] == 1) {
            $usedInForms[$num] = 'show_in_shipping_email';
            return $usedInForms[$num];
        }
        return false;
    }
    /**
     * Set Attribute use in Credit memo Email
     *
     * @param array $data
     * @param string $usedInForms
     * @param int $num
     * @return bool
     */
    private function setUseInMemoEmail($data, $usedInForms, $num)
    {
        if (isset($data['show_in_credit_memo_email']) && $data['show_in_credit_memo_email'] == 1) {
            $usedInForms[$num] = 'show_in_credit_memo_email';
            return $usedInForms[$num];
        }
        return false;
    }
    /**
     * Check permission via ACL resource
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_CustomerAttributes::save');
    }
}
