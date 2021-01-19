<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Ui\Component\Customer\Form;

use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class OrderRestrictionFieldset
 *
 * Component visibility: if the current customer is not sub-user
 */
class OrderRestrictionFieldset extends Fieldset implements ComponentVisibilityInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Bss\CustomerToSubUser\Model\CompanyAccountManagement
     */
    private $companyAccountManagement;

    /**
     * OrderRestrictionFieldset constructor.
     *
     * @param ContextInterface $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyAccountManagement = $companyAccountManagement;
        parent::__construct($context, $components, $data);
    }
    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        $customerId = $this->context->getRequestParam('id');
        if (!$customerId) {
            return false;
        }
        try {
            $customer = $this->customerRepository->getById($customerId);
            $subUser = $this->companyAccountManagement->getCompanyAccountBySubEmail(
                $customer->getEmail(),
                $customer->getWebsiteId()
            );

            if ($subUser->getSubUser()->getSubUserId()) {
                return false;
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
