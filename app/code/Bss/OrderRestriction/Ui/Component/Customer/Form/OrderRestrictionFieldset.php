<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Ui\Component\Customer\Form;

use Bss\OrderRestriction\Helper\ConfigProvider;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Bss\OrderRestriction\Controller\Adminhtml\Customer\QuickSet as ResourceConst;

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
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * OrderRestrictionFieldset constructor.
     *
     * @param ContextInterface $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement
     * @param ConfigProvider $configProvider
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement,
        ConfigProvider $configProvider,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->companyAccountManagement = $companyAccountManagement;
        $this->configProvider = $configProvider;
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }
    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        if (!$this->configProvider->isEnabled() ||
            !$this->authorization->isAllowed(ResourceConst::ADMIN_RESOURCE)
        ) {
            return false;
        }

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
