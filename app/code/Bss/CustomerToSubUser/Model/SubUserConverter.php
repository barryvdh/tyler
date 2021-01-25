<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface as SubUser;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CustomerToSubUser\Helper\MailHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;

/**
 * Class SubUserConverter convert normal customer to sub-user
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SubUserConverter
{
    const SUB_USER_ENABLE = 1;
    const SUB_USER_MAIL_SENT = 1;

    /**
     * Event key
     */
    const CONVERT_EVENT_BEFORE_SAVE_SUB_USER = 'bss_convert_before_save_sub_user';
    const CONVERT_EVENT_AFTER_SAVE_SUB_USER = 'bss_convert_after_save_sub_user';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceModel\Customer
     */
    private $customerResource;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    private $subUserFactory;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var CompanyAccountManagement
     */
    private $companyAccManagement;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Bss\CompanyAccount\Helper\SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var MailHelper
     */
    private $mailHelper;

    /**
     * @var \Bss\CompanyAccount\Helper\EmailHelper
     */
    private $companyEmailHelper;

    /**
     * @param LoggerInterface $logger
     * @param ResourceModel\Customer $customerResource
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory
     * @param SubUserRepositoryInterface $subUserRepository
     * @param EventManager $eventManager
     * @param CompanyAccountManagement $companyAccManagement
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper
     * @param MailHelper $mailHelper
     * @param \Bss\CompanyAccount\Helper\EmailHelper $companyEmailHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomerToSubUser\Model\ResourceModel\Customer $customerResource,
        \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory,
        SubUserRepositoryInterface $subUserRepository,
        EventManager $eventManager,
        CompanyAccountManagement $companyAccManagement,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper,
        MailHelper $mailHelper,
        \Bss\CompanyAccount\Helper\EmailHelper $companyEmailHelper
    ) {
        $this->logger = $logger;
        $this->customerResource = $customerResource;
        $this->subUserFactory = $subUserFactory;
        $this->subUserRepository = $subUserRepository;
        $this->eventManager = $eventManager;
        $this->companyAccManagement = $companyAccManagement;
        $this->request = $request;
        $this->subUserHelper = $subUserHelper;
        $this->mailHelper = $mailHelper;
        $this->companyEmailHelper = $companyEmailHelper;
    }

    /**
     * Convert customer to sub user
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param int $companyAccountId
     * @param int $companyAccountRole
     *
     * @return SubUser|false
     * @throws \Exception
     */
    public function convertToSubUser(
        $customer,
        $companyAccountId,
        $companyAccountRole
    ) {
        try {
            $companyAccountData = $this->companyAccManagement->getCompanyAccountBySubEmail(
                $customer->getEmail(),
                $customer->getWebsiteId()
            );

            $subUser = $companyAccountData->getSubUser();

            if (!$companyAccountId && $subUser->getSubUserId()) {
                $this->companyEmailHelper->sendRemoveNotificationMailToSubUser(
                    null,
                    $subUser
                );

                $this->subUserRepository->deleteById($subUser->getSubUserId());
                return false;
            }

            $needSendMail = $this->isNeedSendMail($subUser, $companyAccountId);

            $subUserData = $subUser->getData();

            if ($companyAccountRole != "") {
                $subUserData[SubUser::ROLE_ID] = (int) $companyAccountRole;
            }
            $subUserData[SubUser::NAME] = $this->getCustomerFullName($customer);
            $subUserData[SubUser::STATUS] = isset($subUserData[SubUser::STATUS]) &&
                $subUserData[SubUser::STATUS] != null ?
                    $subUserData[SubUser::STATUS] : self::SUB_USER_ENABLE;
            $subUserData[SubUser::EMAIL] = $customer->getEmail();
            $subUserData[SubUser::PASSWORD] = $this->customerResource
                ->getEncryptCustomerPassword((int) $customer->getId());
            $subUserData[SubUser::CUSTOMER_ID] = $companyAccountId;
            $subUserData[SubUser::IS_SENT_MAIL] = self::SUB_USER_MAIL_SENT;

            $subUser->setData($subUserData);

            $this->eventManager->dispatch(
                self::CONVERT_EVENT_BEFORE_SAVE_SUB_USER,
                [
                    'object' => $subUser
                ]
            );

            if ($needSendMail) {
                $this->subUserHelper->generateResetPasswordToken($subUser);
            }

            $this->subUserRepository->save($subUser);

            if ($needSendMail) {
                $this->mailHelper->sendConvertCustomerToSubUserWelcomeEmail(
                    $companyAccountId,
                    $subUser
                );
            }

            $this->eventManager->dispatch(
                self::CONVERT_EVENT_AFTER_SAVE_SUB_USER,
                [
                    'object' => $subUser
                ]
            );

            return $subUser;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get need to send mail to subuser
     *
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser
     * @param int $companyAccountId
     * @return bool
     */
    private function isNeedSendMail($subUser, $companyAccountId): bool
    {
        $needSendMail = false;


        if (!$subUser->isSentMail()) {
            $needSendMail = true;
        }

        if ($subUser->isSentMail()) {
            if ($subUser->getCompanyCustomerId() != $companyAccountId) {
                $needSendMail = true;
            }
        }

        return $needSendMail;
    }

    /**
     * Get customer fullname
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return string
     */
    private function getCustomerFullName($customer): string
    {
        $fullName = $customer->getFirstname() . " ";

        if ($customer->getMiddlename()) {
            $fullName .= $customer->getMiddlename() . ' ';
        }

        return $fullName . $customer->getLastname();
    }
}
