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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Controller\Account;

use Bss\B2bRegistration\Helper\CreateAccount;
use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Model\Config\Source\AutoApprovalOptions;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Controller\Result\Forward as ResultForward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class CreatePost
 *
 * @package Bss\B2bRegistration\Controller\Account
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * Template email confirm
     */
    const B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE = 'b2b_email_setting_customer_confirm_templates';

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CreateAccount
     */
    protected $helperCreateAccount;

    /**
     * @var \Bss\B2bRegistration\Helper\CreatePostHelper
     */
    protected $createPostHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Bss\B2bRegistration\Helper\ModuleIntegration
     */
    private $moduleIntegration;

    /**
     * CreatePost constructor.
     * @param Context $context
     * @param Data $helper
     * @param CreateAccount $helperCreateAccount
     * @param \Bss\B2bRegistration\Helper\CreatePostHelper $createPostHelper
     * @param \Bss\B2bRegistration\Helper\ModuleIntegration $moduleIntegration
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Context $context,
        Data $helper,
        CreateAccount $helperCreateAccount,
        \Bss\B2bRegistration\Helper\CreatePostHelper $createPostHelper,
        \Bss\B2bRegistration\Helper\ModuleIntegration $moduleIntegration,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->helperCreateAccount = $helperCreateAccount;
        $this->moduleIntegration = $moduleIntegration;
        $this->createPostHelper = $createPostHelper;
        $this->registry = $registry;
    }

    /**
     * Add address to customer during create account
     *
     * @return \Magento\Customer\Api\Data\AddressInterface|$addressDataObject;
     */
    protected function extractAddress()
    {
        if (!$this->getRequest()->getPost('create_address')) {
            return null;
        }
        $addressForm = $this->helperCreateAccount->getFormFactory()->create(
            'customer_address',
            'customer_register_address'
        );
        $allowedAttributes = $addressForm->getAllowedAttributes();
        $addressData = [];
        $regionDataObject = $this->helperCreateAccount->getRegionDataFactory();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->helperCreateAccount->getDataAddressFactory();
        $this->helper->getDataObject()->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $addressDataObject;
    }

    /**
     * Return customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function returnCustomerSession()
    {
        return $this->helperCreateAccount->getCustomerSessionFactory()->create();
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Create B2b account Action
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->checkLogin();

        if (!$this->getRequest()->isPost()
            || !$this->createPostHelper->returnValidator()->validate($this->getRequest())
        ) {
            $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('*/*/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }

        $autoApproval = $this->helper->isAutoApproval();
        $customerSession = $this->returnCustomerSession();
        $customerSession->regenerateId();

        try {
            $this->registry->unregister('bss_b2b_account');
            $this->registry->register('bss_b2b_account', 'true');
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];
            $customer = $this->helper->getCustomerExtractor()->extract(
                $this->getFormExtract(),
                $this->_request
            );
            $customer->setAddresses($addresses);
            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $customerSession->getBeforeAuthUrl();
            $this->checkPasswordConfirmation($password, $confirmation);
            $this->saveGroupAttribute($customer);
            $this->storageCustomerStatus($customer, $autoApproval);
            $customer = $this->createPostHelper->returnAccountManagement()
                ->createAccount($customer, $password, $redirectUrl);
            $this->subcribeCustomer($customer);

            $this->_eventManager->dispatch(
                'bss_customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $resultRedirect = $this->getReturnType($customer, $autoApproval, $resultRedirect);

            return $resultRedirect;
        } catch (StateException $e) {
            $url = $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t save the customer.'));
        }

        $customerSession->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->createPostHelper
            ->returnUrlFactory()
            ->create()
            ->getUrl('btwob/account/create', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
    }

    /**
     * Get form extract
     *
     * @return string
     */
    protected function getFormExtract()
    {
        if ($this->moduleIntegration->isBssCustomerAttributesModuleEnabled()) {
            return 'b2b_account_create';
        }
        return 'customer_account_create';
    }

    /**
     * Save status for customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param bool $autoApproval
     */
    private function storageCustomerStatus($customer, $autoApproval)
    {
        if ($autoApproval) {
            $customer->setCustomAttribute("b2b_activasion_status", $this->createPostHelper->returnApproval());
        } else {
            $customer->setCustomAttribute("b2b_activasion_status", $this->createPostHelper->returnPending());
        }
    }

    /**
     * Check Customer Login
     *
     * @return Redirect
     */
    protected function checkLogin()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->returnCustomerSession()->isLoggedIn()) {
            $resultRedirect->setPath('customer/account/index');
            return $resultRedirect;
        }
    }

    /**
     * Check subcribe
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    protected function subcribeCustomer($customer)
    {
        if ($this->getRequest()->getParam('is_subscribed', false)) {
            $this->helperCreateAccount->getSubscriberFactory()->subscribeCustomerById($customer->getId());
        }
    }

    /**
     * Save B2b Customer Group
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    protected function saveGroupAttribute($customer)
    {
        $customerGroupId = $this->helper->getCustomerGroup();
        $tax = $this->getRequest()->getPostValue('taxvat');
        $gender = $this->getRequest()->getPostValue('gender');

        if ($tax) {
            $customer->setTaxvat($tax);
        }
        if ($gender) {
            $customer->setGender($gender);
        }
        if (!$this->helper->isAutoAssigCustomerGroup()) {
            $customer->setGroupId($customerGroupId);
        }
    }

    /**
     * Return success message
     *
     * @return string
     * @throws NoSuchEntityException
     */
    protected function getSuccessMessage()
    {
        if ($this->helperCreateAccount->getAddressHelper()->isVatValidationEnabled()) {
            if ($this->helperCreateAccount->getAddressHelper()
                    ->getTaxCalculationAddressType() == $this->createPostHelper->returnTypeShipping()
            ) {
                // @codingStandardsIgnoreStart
                $message = sprintf(
                    'If you are a registered VAT customer, please <a href="%s">click here</a> to enter your shipping address for proper VAT calculation.',
                    $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/address/edit')
                );
            // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = sprintf(
                    'If you are a registered VAT customer, please <a href="%s">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->createPostHelper->returnUrlFactory()->create()->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $storeName = $this->helper->getStoreName();
            $message = sprintf('Thank you for registering with %s.', $storeName);
        }
        return $message;
    }

    /**
     * @param $customer
     * @param $autoApproval
     * @param $resultRedirect
     * @return ResultForward|Redirect
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getReturnType($customer, $autoApproval, $resultRedirect)
    {
        $confirmationStatus = $this->createPostHelper
            ->returnAccountManagement()
            ->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === $this->createPostHelper->returnConfirmRequire()) {
            $emailUrl = $this->helper->getEmailConfirmUrl($customer->getEmail());
            // @codingStandardsIgnoreStart
            $this->messageManager->addSuccess(
                __(
                    'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                    $emailUrl
                )
            );

            $this->sendNewAccountEmail($customer, $autoApproval);

            // @codingStandardsIgnoreEnd
            $url = $this->createPostHelper
                ->returnUrlFactory()
                ->create()
                ->getUrl('customer/account/login', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        } elseif ($autoApproval) {
            $this->returnCustomerSession()->setCustomerDataAsLoggedIn($customer);
            $this->sendNewAccountEmail($customer, $autoApproval);
            $this->messageManager->addSuccess(__($this->getSuccessMessage()));
            $resultRedirect = $this->callBackUrl($resultRedirect);
            return $resultRedirect;
        } else {
            $message = $this->helper->getPendingMess();
            $this->messageManager->addSuccess($message);
            $this->sendNewAccountEmail($customer, $autoApproval);
            if ($this->helper->isEnableConfirmEmail()) {
                $this->sendMailConfirmToCustomer($customer);
            }
            $url = $this->createPostHelper
                ->returnUrlFactory()
                ->create()
                ->getUrl('customer/account/login', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->success($url));
            return $resultRedirect;
        }
    }

    /**
     * Call back url
     *
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    protected function callBackUrl($resultRedirect)
    {
        $requestedRedirect = $this->createPostHelper->returnAccountRedirect()->getRedirectCookie();
        if (!$this->helperCreateAccount->getScopeConfig()->getValue('customer/startup/redirect_dashboard') &&
            $requestedRedirect
        ) {
            $resultRedirect->setUrl($this->_redirect->success($requestedRedirect));
            $this->createPostHelper->returnAccountRedirect()->clearRedirectCookie();
            return $resultRedirect;
        }
        return $this->createPostHelper->returnAccountRedirect()->getRedirect();
    }

    /**
     * @param CustomerInterface $customer
     * @param bool $autoApproval
     * @throws NoSuchEntityException
     */
    protected function sendNewAccountEmail($customer, $autoApproval)
    {
        $customerEmail = $customer->getEmail();

        $storeId = $this->helper->getStoreId();
        $emailTemplate = $this->helper->getAdminEmailTemplate();
        $fromEmail = $this->helper->getAdminEmailSender();
        $recipient = $this->helper->getAdminEmail();
        $recipient = str_replace(' ', '', $recipient);
        $recipient = (explode(',', $recipient));
        $emailVar = [
            'varEmail'  => $customerEmail
        ];

        $adminSendMailStatus = explode(',', $this->helper->isEnableAdminEmail());

        if (($autoApproval && in_array(AutoApprovalOptions::AUTO_APPROVE_ACC, $adminSendMailStatus)) ||
            (!$autoApproval && in_array(AutoApprovalOptions::NOT_AUTO_APPROVE_ACC, $adminSendMailStatus))) {
            $this->createPostHelper
                ->returnBssHelperEmail()
                ->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
        }
    }

    /**
     * Send email confirm to customer
     *
     * @param object $customer
     * @throws NoSuchEntityException
     */
    protected function sendMailConfirmToCustomer($customer)
    {
        $customerEmail = $customer->getEmail();
        $customerName = $customer->getFirstName().' '.$customer->getLastName();
        $storeId = $this->helper->getStoreId();
        $emailTemplate = self::B2B_EMAIL_CONFIRM_CUSTOMER_TEMPLATE;
        $fromEmail = $this->helper->getAdminEmailSender();
        $recipient = $customerEmail;
        $emailVar = [
            'varName'  => $customerName
        ];
        $this->createPostHelper
            ->returnBssHelperEmail()
            ->sendEmail($fromEmail, $recipient, $emailTemplate, $storeId, $emailVar);
    }
}
