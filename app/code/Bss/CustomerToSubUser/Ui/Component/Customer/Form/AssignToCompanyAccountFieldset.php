<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Ui\Component\Customer\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Psr\Log\LoggerInterface;
use Bss\CompanyAccount\Helper\Data as CompanyAccountHelper;

/**
 * Assign To Company account fieldset element
 */
class AssignToCompanyAccountFieldset extends Fieldset implements ComponentVisibilityInterface
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CompanyAccountHelper
     */
    private $companyAccountHelper;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        ContextInterface $context,
        CompanyAccountHelper $companyAccountHelper,
        array $components = [],
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->companyAccountHelper = $companyAccountHelper;
        parent::__construct($context, $components, $data);
    }

    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        $customerId = $this->context->getRequestParam('id');
        if (!$customerId) {
            return true;
        }
        try {
            $customer = $this->customerRepository->getById($customerId);
            return !$this->companyAccountHelper->isEnable($customer->getWebsiteId()) ||
                !$this->companyAccountHelper->isCompanyAccount($customer);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }
}
