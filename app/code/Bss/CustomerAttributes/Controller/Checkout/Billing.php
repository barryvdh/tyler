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
namespace Bss\CustomerAttributes\Controller\Checkout;


use Bss\CustomerAttributes\Helper\GetHtmltoEmail;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Billing
 *
 */
class Billing extends \Magento\Multishipping\Controller\Checkout\Billing
{
    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var \Bss\CustomerAttributes\Helper\Customerattribute
     */
    private $helper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param AddressRepositoryInterface $addressRepository
     * @param Json $json
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $helper
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        AddressRepositoryInterface $addressRepository,
        Json $json,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Bss\CustomerAttributes\Helper\Customerattribute $helper,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
        $this->addressRepository = $addressRepository;
        $this->quoteRepository = $quoteRepository;
        $this->json = $json;
        $this->helper = $helper;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if (!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/checkout_address/selectBilling');
            return false;
        }
        $quote = $this->_getCheckout()->getQuote();
        $customerAddressId = $quote->getBillingAddress()->getCustomerAddressId();
        $addresses = $this->addressRepository
            ->getById($customerAddressId)->getCustomAttributes();
        $customAddressAttribute = [];
        foreach ($addresses as $attributeCode => $attribute) {
            $addressAttribute = $this->attributeRepository
                ->get('customer_address', $attributeCode);
            $addressValue = $this->helper->getValueAddressAttributeForOrder(
                $addressAttribute,
                $attribute->getValue()
            );
            $value = [
                'value' => $addressValue,
                'label' => $addressAttribute->getFrontendLabel()
            ];
            $customAddressAttribute[$attributeCode] = $value;
        }
        $jsonAddress = $this->json->serialize($customAddressAttribute);
        $quote->getBillingAddress()->setCustomerAddressAttribute($jsonAddress);
        $quote->setDataChanges(true);
        $this->quoteRepository->save($quote);
        return true;
    }
}
