<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Observer;

use Bss\CompanyAccount\Api\Data\SubUserInterface as SubUser;
use Bss\CustomerToSubUser\Model\SubUserConverter;
use Magento\Framework\Event\Observer;

/**
 * Class AssignAsSubUser
 */
class AssignAsSubUserObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\RequestFactory
     */
    private $requestFactory;

    /**
     * @var \Bss\CompanyAccount\Helper\SubUserHelper
     */
    private $subUserHelper;

    /**
     * @var SubUserConverter
     */
    protected SubUserConverter $subUserConverter;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\RequestFactory $requestFactory,
        \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper,
        SubUserConverter $subUserConverter
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->requestFactory = $requestFactory;
        $this->subUserHelper = $subUserHelper;
        $this->subUserConverter = $subUserConverter;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $savedCustomer */
            $savedCustomer = $observer->getData("customer_data_object");
            $assignToSubUserParams = $this->request->getPostValue("assign_to_company_account");

            if (!$assignToSubUserParams['company_account_id']) {
                return $this;
            }

            if ($savedCustomer && $assignToSubUserParams) {
                $this->subUserConverter->convertToSubUser(
                    $savedCustomer,
                    $assignToSubUserParams['company_account_id'],
                    $assignToSubUserParams['company_account_roles']
                );
                return $this;
                $companyAccountId = $assignToSubUserParams['company_account_id'];
                $createSubUserRequest = $this->requestFactory->create();

                $assignToSubUserParams = [
                    SubUser::NAME => $this->getCustomerFullName($savedCustomer),
                    SubUser::ROLE_ID => $assignToSubUserParams['company_account_roles'],
                    SubUser::STATUS => self::SUB_USER_ENABLE,
                    SubUser::EMAIL => $savedCustomer->getEmail()
                ];
                $createSubUserRequest->setParams($assignToSubUserParams);
                $emailErrorMsg = "";
                $message = $this->subUserHelper->createSubUser(
                    $createSubUserRequest,
                    $companyAccountId,
                    $emailErrorMsg
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }


}
