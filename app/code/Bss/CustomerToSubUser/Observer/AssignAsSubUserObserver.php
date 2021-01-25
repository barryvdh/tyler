<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Observer;

use Bss\CustomerToSubUser\Model\SubUserConverter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class AssignAsSubUser - Assign saved customer as sub-user if be set
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
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
    private $subUserConverter;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * AssignAsSubUserObserver constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\RequestFactory $requestFactory
     * @param \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper
     * @param SubUserConverter $subUserConverter
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\RequestFactory $requestFactory,
        \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper,
        SubUserConverter $subUserConverter,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->requestFactory = $requestFactory;
        $this->subUserHelper = $subUserHelper;
        $this->subUserConverter = $subUserConverter;
        $this->messageManager = $messageManager;
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

            if ($assignToSubUserParams &&
                !$assignToSubUserParams['sub_id'] &&
                !$assignToSubUserParams['company_account_id'] &&
                !$assignToSubUserParams['role_id']
            ) {
                return $this;
            }

            if ($savedCustomer && $assignToSubUserParams) {
                $customerParams = $this->request->getParam('customer');
                $customerParams['entity_id'] = $savedCustomer->getId();
                $this->request->setParams(['customer' => $customerParams]);

                $subUser = $this->subUserConverter->convertToSubUser(
                    $savedCustomer,
                    $assignToSubUserParams['company_account_id'],
                    $assignToSubUserParams['role_id'] ?? ""
                );

                if ($subUser) {
                    $assignToSubUserParams['sub_id'] = $subUser->getSubUserId();

                    $this->request->setParams(
                        [
                            'assign_to_company_account' => $assignToSubUserParams
                        ]
                    );
                }

                return $this;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __("Something went wrong while convert to sub-user. Please review the log!")
            );
        }

        return $this;
    }
}
