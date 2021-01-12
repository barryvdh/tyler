<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\SubUser;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\View\Element\Template;

/**
 * Class Edit
 *
 * @package Bss\CompanyAccount\Block\SubUser
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Edit extends Template
{
    /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface */
    protected $subUser = null;

    /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface */
    protected $oldSubUser = null;

    /**
     * @var SubUserRepositoryInterface
     */
    private $subUserRepository;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory
     */
    private $subUserFactory;

    /**
     * @var SubRoleRepositoryInterface
     */
    private $subRoleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var SessionManager
     */
    private $coreSession;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Edit constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubRoleRepositoryInterface $subRoleRepository
     * @param Data $helper
     * @param SerializerInterface $serializer
     * @param \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        SubUserRepositoryInterface $subUserRepository,
        SubRoleRepositoryInterface $subRoleRepository,
        Data $helper,
        SerializerInterface $serializer,
        \Bss\CompanyAccount\Api\Data\SubUserInterfaceFactory $subUserFactory,
        Session $customerSession,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->subUserRepository = $subUserRepository;
        $this->customerSession = $customerSession;
        $this->subUserFactory = $subUserFactory;
        $this->subRoleRepository = $subRoleRepository;
        $this->criteriaBuilder = $this->helper->getDataHelper()->getSearchCriteriaBuilder();
        $this->coreSession =  $this->helper->getDataHelper()->getCoreSession();
        $this->filterBuilder =  $this->helper->getDataHelper()->getFilterBuilder();
        $this->filterGroupBuilder =  $this->helper->getDataHelper()->getFilterGroupBuilder();
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Prepare render layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->initOldSubUser();
        $this->initSubUser();
        $this->compareWithOldData();
        $this->pageConfig->getTitle()->set($this->getTitle());
    }

    /**
     * Initialize sub-user object
     *
     * @return void
     */
    private function initSubUser()
    {
        if ($subId = $this->getRequest()->getParam('sub_id')) {
            try {
                $this->subUser = $this->subUserRepository->getById($subId);
                if ($this->subUser->getCompanyCustomerId() != $this->customerSession->getCustomerId()) {
                    $this->subUser = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->subUser = null;
            }
        }
        if ($this->subUser === null || !$this->subUser->getSubUserId()) {
            $this->subUser = $this->subUserFactory->create();
        }
    }

    /**
     * Is disable email field
     *
     * If form page is edit page
     *
     * @return bool
     */
    public function isDisabledEmail()
    {
        return $this->getSubUser()->getSubUserId();
    }

    /**
     * Get sub-user session data
     *
     * @return $this
     */
    private function initOldSubUser()
    {
        /** @var \Zend\Stdlib\Parameters $data */
        $data = $this->coreSession->getSubUserFormData();
        if ($data) {
            $this->oldSubUser = $this->subUserFactory->create();
            $this->oldSubUser->setSubUserStatus($data->get('sub_status'));
            $this->oldSubUser->setSubUserName($data->get('sub_name'));
            $this->oldSubUser->setSubUserEmail($data->get('sub_email'));
            $this->oldSubUser->setRoleId($data->get('role_id'));
            $this->coreSession->unsSubUserFormData();
        } else {
            $this->oldSubUser = null;
        }
        return $this;
    }

    /**
     * Compare sub-user with old sub-user data
     *
     * @return $this
     */
    public function compareWithOldData()
    {
        if ($oldData = $this->getOldSubUser()) {
            $this->subUser = $oldData;
        }
        return $this;
    }

    /**
     * Get option for status select
     *
     * @return string
     */
    public function getEnableDisableOptions()
    {
        $options = [
            1 => [
                'label' => __('Enable'),
                'value' => 1,
                'selected' => $this->getSubUser()->getSubUserStatus() ? 'selected' : ''
            ],
            0 => [
                'label' => __('Disable'),
                'value' => 0,
                'selected' => !$this->getSubUser()->getSubUserStatus() ? 'selected' : ''
            ]
        ];
        return $this->getSelectOptions($options);
    }

    /**
     * Get option for status select
     *
     * @return string
     */
    public function getRoleOptions()
    {
        $data = $this->getRoleData();
        $options = [];

        foreach ($data as $role) {
            $options[$role->getRoleId()] = [
                'label' => $role->getRoleName(),
                'value' => $role->getRoleId(),
                'selected' => ''
            ];

            if ($this->getSubUser()->getRelatedRoleId() == $role->getRoleId()
            ) {
                $options[$role->getRoleId()]['selected'] = 'selected';
            }
        }
        return $this->getSelectOptions($options);
    }

    /**
     * Get role list
     *
     * @return \Bss\CompanyAccount\Api\Data\SubRoleInterface[]
     */
    public function getRoleData()
    {
        /* Use filter group for OR condition */
        $customerFilter1 = $this->filterBuilder
            ->setField('customer_id')
            ->setConditionType('eq')
            ->setValue($this->customerSession->getCustomerId())
            ->create();
        $customerFilter2 = $this->filterBuilder
            ->setField('customer_id')
            ->setConditionType('null')
            ->setValue(null)
            ->create();
        $filterGroup = $this->filterGroupBuilder
            ->addFilter($customerFilter1)
            ->addFilter($customerFilter2)
            ->create();
        $this->criteriaBuilder->setFilterGroups([$filterGroup]);

        return $this->subRoleRepository->getList(
            $this->criteriaBuilder->create()
        )->getItems();
    }

    /**
     * Get content of select option
     *
     * @param array $options
     * @return string
     */
    protected function getSelectOptions(array $options)
    {
        $htmlContent = '';
        foreach ($options as $option) {
            $htmlContent .= '<option value="'
                . $option['value'] . '" '
                . $option['selected'] . '>'
                . $option['label']
                . '</option>';
        }
        return $htmlContent;
    }

    /**
     * Return the title, either editing an existing address, or adding a new one.
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->getSubUser()->getSubUserId()) {
            $title = __('Add New Sub-user');
        } else {
            $title = __('Edit %1', $this->getSubUser()->getSubUserName());
        }
        return $title;
    }

    /**
     * Get Sub-user object
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getSubUser()
    {
        return $this->subUser;
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getSubUser()->getCompanyCustomerId() !== 0 ?
            $this->getSubUser()->getCompanyCustomerId() :
            $this->customerSession->getCustomerId();
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'companyaccount/subuser/formPost',
            ['_secure' => true, 'sub_id' => $this->getSubUser()->getSubUserId()]
        );
    }

    /**
     * Get old sub-user
     *
     * @return \Bss\CompanyAccount\Api\Data\SubUserInterface
     */
    public function getOldSubUser()
    {
        return $this->oldSubUser;
    }

    /**
     * Return the Url to go back.
     *
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getData('back_url')) {
            return $this->getData('back_url');
        }

        return $this->getUrl('companyaccount/subuser/');
    }

    /**
     * Get validate unique email url
     *
     * @return string
     */
    public function getValidateUniqueSubMailUrl()
    {
        return $this->_urlBuilder->getUrl(
            'companyaccount/subuser/ValidateUniqueMail',
            ['_secure' => true, 'customer_id' => $this->getCustomerId()]
        );
    }

    /**
     * Get Serializer object
     *
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
}
