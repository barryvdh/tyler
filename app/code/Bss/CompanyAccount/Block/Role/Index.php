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
namespace Bss\CompanyAccount\Block\Role;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface as RoleRepository;
use Bss\CompanyAccount\Helper\CheckAssignedRole;
use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\ResourceModel\SubRole\CollectionFactory as Collection;
use Magento\Framework\View\Element\Template;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Block\Role
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var Collection
     */
    private $roleCollection;

    /**
     * @var CheckAssignedRole
     */
    private $checkAssignedRole;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Index constructor.
     *
     * @param Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param RoleRepository $roleRepository
     * @param Data $helper
     * @param CheckAssignedRole $checkAssignedRole
     * @param Collection $roleCollection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        RoleRepository $roleRepository,
        Data $helper,
        CheckAssignedRole $checkAssignedRole,
        Collection $roleCollection,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->roleRepository = $roleRepository;
        $this->roleCollection = $roleCollection;
        $this->checkAssignedRole = $checkAssignedRole;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Manage sub-user constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $collection = $this->roleCollection->create();

        $collection->addFieldToFilter(
            [
                'customer_id',
                'customer_id'
            ],
            [
                ['eq' => $this->currentCustomer->getCustomerId()],
                ["null" => true]
            ]
        )
        ->addOrder('role_id', 'desc');
        $this->setItems($collection);
    }

    /**
     * Convert amount currency
     *
     * @param float $amount
     * @return float|string
     */
    public function convertCurrency($amount)
    {
        if (empty($amount)) {
            return '';
        }
        return $this->helper->convertFormatCurrency($amount);
    }

    /**
     * Enter description here...
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(
            \Magento\Theme\Block\Html\Pager::class,
            'companyaccount.role.index'
        )->setCollection(
            $this->getItems()
        )->setPath('companyaccount/role/');
        $this->setChild('pager', $pager);
        $this->getItems()->load();

        return $this;
    }

    /**
     * Format permissions
     *
     * Remove basic and advanced role group val
     *
     * @param string $permissionString
     * @return string
     */
    public function formatPermission($permissionString)
    {
        $roleType = explode(',', $permissionString);
        $roleType = array_filter($roleType, function ($value) {
            return (int) $value > 0;
        });
        return implode(',', $roleType);
    }

    /**
     * Get edit link
     *
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterface $role
     * @return string
     */
    public function getEditUrl($role)
    {
        return $this->getUrl('companyaccount/role/edit', ['role_id' => $role->getRoleId()]);
    }

    /**
     * Get create url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('companyaccount/role/create');
    }

    /**
     * Get delete link
     *
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterface $role
     * @return string
     */
    public function getDeleteUrl($role)
    {
        return $this->getUrl('companyaccount/role/delete', ['role_id' => $role->getRoleId()]);
    }
}
