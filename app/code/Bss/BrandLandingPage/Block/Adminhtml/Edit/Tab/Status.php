<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Block\Adminhtml\Edit\Tab;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Address\Mapper;

/**
 * Class Status
 * Get signup sources label for customer
 */
class Status extends \Bss\B2bRegistration\Block\Adminhtml\Edit\Tab\View\Status
{
    /**
     * @param \Bss\BrandLandingPage\Model\Customer\Attribute\Source\Brand
     */
    private $brand;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Registry $registry
     * @param Mapper $addressMapper
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Logger $customerLogger
     * @param \Bss\BrandLandingPage\Model\Customer\Attribute\Source\Brand $brandSource
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        AccountManagementInterface $accountManagement,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Registry $registry,
        Mapper $addressMapper,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Logger $customerLogger,
        \Bss\BrandLandingPage\Model\Customer\Attribute\Source\Brand $brandSource,
        array $data = []
    ) {
        $this->brand = $brandSource;
        parent::__construct(
            $context,
            $accountManagement,
            $groupRepository,
            $customerDataFactory,
            $addressHelper,
            $dateTime,
            $registry,
            $addressMapper,
            $dataObjectHelper,
            $customerLogger,
            $data
        );
    }

    /**
     * Get signup sources label
     *
     * @return string
     */
    public function getSignupSourcesLabel()
    {
        $signupSources = $this->getCustomer()->getCustomAttribute('signup_sources');

        if ($signupSources) {
            return $this->brand->getOptionText($signupSources->getValue());
        }

        return __("Normal");
    }
}
