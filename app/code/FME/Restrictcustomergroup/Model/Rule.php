<?php
/**
 * Class for Restrictcustomergroup Rule
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Model;

class Rule extends \Magento\Rule\Model\AbstractModel
{
    /**
     * @var \FME\Restrictcustomergroup\Model\Rule\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Action\CollectionFactory
     */
    protected $_actionCollectionFactory;

    /*     * #@+
     * Page's Statuses
     */

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const MODE_ERROR = 1;
    const MODE_REDIRECT = 2;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \FME\Restrictcustomergroup\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\CatalogRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
    }

    protected function _construct()
    {
        $this->_init('FME\Restrictcustomergroup\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Prepare rule's statuses.
     *
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare rule's mode.
     *
     *
     * @return array
     */
    public function getAvailableModes()
    {
        return [self::MODE_ERROR => __('Error Message'), self::MODE_REDIRECT => __('Redirect')];
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * Getter for rule actions collection
     *
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * Get catalog rule customer group Ids
     *
     * @return array|null
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }

    public function getRelatedBlocks($ruleId)
    {
        $blockTable = $this->getResource()->getTable('fme_restrictcustomergroup_block');
        $collection = $this->getResourceCollection()
                ->addFieldToFilter('main_table.rule_id', $ruleId);
        $collection->getSelect()
                ->joinLeft(['related' => $blockTable], 'main_table.rule_id = related.rule_id')
                ->order('main_table.rule_id');
        return $collection->getData();
    }
}
