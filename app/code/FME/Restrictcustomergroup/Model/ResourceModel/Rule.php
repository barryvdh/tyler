<?php
/**
 * Class for Restrictcustomergroup Rule
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Model\ResourceModel;

use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Stdlib\DateTime;
use \Magento\Framework\Model\ResourceModel\Db\Context;

class Rule extends \FME\Restrictcustomergroup\Model\ResourceModel\AbstractResource
{

    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    protected $_objectManager;
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table' => 'fme_restrictcustomergroup_store',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'store_id',
        ],
        'customer_group' => [
            'associations_table' => 'fme_restrictcustomergroup_customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id',
        ]
    ];

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $connectionName = null
    ) {

        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->_objectManager = $objectManager;
    }

    protected function _construct()
    {
        $this->_init('fme_restrictcustomergroup_rule', 'rule_id');
    }

    /**
     * Process page data before deleting
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $condition = ['rule_id = ?' => (int) $object->getId()];

        $this->getConnection()->delete($this->getTable('fme_restrictcustomergroup_store'), $condition);

        $this->getConnection()->delete($this->getTable('fme_restrictcustomergroup_customer_group'), $condition);

        return parent::_beforeDelete($object);
    }

    /**
     * Perform operations after object load
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId())
        {
            $object->setData('store_ids', (array) $this->getStoreIds($object->getId()));
            $object->setData('customer_group_ids', (array) $this->getCustomerGroupIds($object->getId()));
        }
        return parent::_afterLoad($object);
    }


    /**
     * Assign page to store views
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {

        if ($object->hasStoreIds()) {
            $storeIds = $object->getStoreIds();
            if (!is_array($storeIds)) {
                $storeIds = explode(',', (string) $storeIds);
            }

            $this->bindRuleToEntity($object->getId(), $storeIds, 'store');
        }

        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string) $customerGroupIds);
            }

            $this->bindRuleToEntity($object->getId(), $customerGroupIds, 'customer_group');
        }

        $links = $object['links'];
        if (isset($links['related'])) {
            $condition = $this->getConnection()
                    ->quoteInto('rule_id = ?', $object->getId());

            $blockIds = $this->_objectManager->get('Magento\Backend\Helper\Js')
                    ->decodeGridSerializedInput($links['related']);
            $this->getConnection()
                    ->delete($this->getTable('fme_restrictcustomergroup_block'), $condition);

            foreach ($blockIds as $id) {
                $blocks = [];
                $blocks['rule_id'] = $object->getId();
                $blocks['block_id'] = $id;
                $this->getConnection()
                        ->insert($this->getTable('fme_restrictcustomergroup_block'), $blocks);
            }
        }

        parent::_afterSave($object);
        return $this;
    }
}
