<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Helper;

use Bss\CustomerToSubUser\Exception\CannotSendEmailException as CannotSendEmail;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Email helper
 */
class MailHelper
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Bss\CompanyAccount\Helper\Data
     */
    private $companyAccountHelper;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Store\Model\StoreManager|StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\CompanyAccount\Helper\GetType
     */
    private $getType;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Bss\CompanyAccount\Helper\Data $companyAccountHelper,
        ConfigProvider $configProvider,
        \Bss\CompanyAccount\Helper\GetType $getType,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->companyAccountHelper = $companyAccountHelper;
        $this->configProvider = $configProvider;
        $this->storeManager = $getType->getStoreManager();
        $this->getType = $getType;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Get customer object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface|int $customer
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getCustomer($customer)
    {
        if ($customer instanceof \Magento\Customer\Api\Data\CustomerInterface) {
            return $customer;
        }

        return $this->customerRepository->getById($customer);

    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface|int $customer
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendConvertCustomerToSubUserWelcomeEmail($customer, $subUser)
    {
        try {
            $customer = $this->getCustomer($customer);
            // $subUser = $this->getSubUser($subUser);
            $storeId = $customer->getStoreId();
            $store = $this->storeManager->getStore($storeId);
            $this->sendMail(
                $subUser->getSubUserEmail(),
                null,
                $this->configProvider->getConvertToSubUserEmailTemplate(),
                [
                    'area' => $this->getType->getAreaFrontend(),
                    'store' => $this->storeManager->getStore()->getId()
                ],
                [
                    'subUser' => $subUser,
                    'store' => $store,
                    'companyAccountEmail' => $customer->getEmail()
                ]
            );
        } catch (CannotSendEmail $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CannotSendEmail(__($e->getMessage()));
        }
    }

    /**
     * Send email
     *
     * @param string|null $receiver
     * @param string|null $ccMails
     * @param string $mailTemplate
     * @param array $options
     * @param array $vars
     *
     * @return bool
     * @throws CannotSendEmail
     */
    protected function sendMail($receiver = null, $ccMails = null, $mailTemplate = '', $options = [], $vars = [])
    {
        try {
            $senderEmail = $this->companyAccountHelper->getEmailSender();
            $senderName = $this->companyAccountHelper->getEmailSenderName();
            $sender = [
                'name' => $senderName,
                'email' => $senderEmail,
            ];
            $this->inlineTranslation->suspend();
            $this->transportBuilder
                ->setTemplateIdentifier($mailTemplate)
                ->setTemplateOptions($options)
                ->setTemplateVars($vars)
                ->setFrom($sender)
                ->addTo($receiver);
            if ($ccMails !== null) {
                if (strpos($ccMails, ',') !== false) {
                    $ccMails = explode(',', $ccMails);
                    foreach ($ccMails as $mail) {
                        trim($mail) !== "" ? $this->transportBuilder->addCc(trim($mail)) : false;
                    }
                } else {
                    $this->transportBuilder->addCc(trim($ccMails));
                }
            }
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CannotSendEmail(__('We can\'t send email now. Please review the log.'));
        }
    }
}
