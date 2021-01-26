<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Observer;

use Bss\CustomerToSubUser\Model\ResourceModel\SubUser as SubUserResource;
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
     * @var \Bss\CompanyAccount\Api\SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var SubUserResource
     */
    private $subUserResource;

    /**
     * AssignAsSubUserObserver constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\RequestFactory $requestFactory
     * @param \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper
     * @param SubUserConverter $subUserConverter
     * @param ManagerInterface $messageManager
     * @param \Bss\CompanyAccount\Api\SubUserRepositoryInterface $subUserRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param SubUserResource $subUserResource
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\RequestFactory $requestFactory,
        \Bss\CompanyAccount\Helper\SubUserHelper $subUserHelper,
        SubUserConverter $subUserConverter,
        ManagerInterface $messageManager,
        \Bss\CompanyAccount\Api\SubUserRepositoryInterface $subUserRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        SubUserResource $subUserResource
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->requestFactory = $requestFactory;
        $this->subUserHelper = $subUserHelper;
        $this->subUserConverter = $subUserConverter;
        $this->messageManager = $messageManager;
        $this->subUserRepository = $subUserRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->subUserResource = $subUserResource;
    }

    /**
     * @inheritDoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $savedCustomer */
            $savedCustomer = $observer->getData("customer_data_object");

            /** @var \Magento\Customer\Model\Customer $deletedCustomer */
            if ($deletedCustomer = $observer->getData('data_object')) {
                if ($deletedCustomer->isDeleted()) {
                    $subUser = $this->subUserResource->getSubUserByEmailAndWebsiteId(
                        $deletedCustomer->getEmail(),
                        $deletedCustomer->getWebsiteId()
                    );

                    if ($subUser->getId()) {
                        $this->subUserRepository->delete($subUser);

                        return $this;
                    }
                }
            }

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
