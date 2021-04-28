<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Plugin\Customer\Adminhtml;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CustomerToSubUser\Exception\CannotSendEmailException;
use Bss\CustomerToSubUser\Model\SubUserConverter;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;

/**
 * Class Save
 * Convert to subuser and redirect to customer list in admin
 */
class Save
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SubUserConverter
     */
    protected $subUserConverter;
    /**
     * @var MessageManagerInterface
     */
    protected $messageMessenger;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * Save constructor.
     *
     * @param \Magento\Framework\Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubUserConverter $subUserConverter
     * @param MessageManagerInterface $messageMessenger
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        SubUserConverter $subUserConverter,
        MessageManagerInterface $messageMessenger,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->subUserConverter = $subUserConverter;
        $this->messageMessenger = $messageMessenger;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Convert to sub-user n del the current customer
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     * @param Redirect $result
     * @return Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function afterExecute(
        \Magento\Customer\Controller\Adminhtml\Index\Save $subject,
        $result
    ) {
        $request = $subject->getRequest();
        $assignToSubUserParams = $request->getPostValue("assign_to_company_account");

        if ($assignToSubUserParams &&
            !$assignToSubUserParams['sub_id'] &&
            !$assignToSubUserParams['company_account_id'] &&
            !$assignToSubUserParams['role_id']
        ) {
            return $result;
        }

        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
            } catch (NoSuchEntityException $e) {
                $this->messageMessenger->addErrorMessage(
                    __("Error when convert customer to sub-user: " . $e->getMessage())
                );
            } catch (LocalizedException $e) {
                $this->messageMessenger->addErrorMessage(
                    __("Error when convert customer to sub-user: " . $e->getMessage())
                );
            }


            if (!isset($customer)) {
                return $result;
            }

            if ($customer->getId() && $assignToSubUserParams) {
                try {
                    $subUser = $this->subUserConverter->convertToSubUser(
                        $customer,
                        $assignToSubUserParams['company_account_id'],
                        $assignToSubUserParams['role_id'] ?? ""
                    );
                } catch (CannotSendEmailException $e) {
                    $this->messageMessenger->addErrorMessage(
                        __($e->getMessage())
                    );
                } catch (\Exception $e) {
                    return $result;
                }

                if (isset($subUser) && $subUser) {
                    try {
                        $this->customerRepository->deleteById($customerId);
                    } catch (\Exception $e) {
                        $this->subUserRepository->deleteById($subUser->getSubUserId());
                        $this->messageMessenger->addErrorMessage(
                            __("Error when convert customer to sub-user: " . $e->getMessage())
                        );

                        return $result;
                    }
                    $this->messageMessenger->getMessages(true);
                    $this->messageMessenger->addSuccessMessage(
                        "The customer have assigned as sub-user." .
                        " You can manage it from sub-user manage in company account tab."
                    );
                    $returnToEdit = (bool)$request->getParam('back', false);
                    $result->setPath("customer/");
                    if ($returnToEdit) {
                        $result->setPath(
                            "customer/*/edit",
                            ["id" => $subUser->getCompanyCustomerId(), '_current' => true]
                        );
                    }
                }
            }
        }

        return $result;
    }
}
