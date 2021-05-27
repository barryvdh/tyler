<?php
/**
 * Class for Restrictcustomergroup Collection
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Model\ResourceModel\Rule;

//use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param mixed $connection
     * @param \Magento\Framework\Model\Resource\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init(
            'FME\Restrictcustomergroup\Model\Rule',
            'FME\Restrictcustomergroup\Model\ResourceModel\Rule'
        );
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addAttributeInConditionFilter($attributeCode)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $match = sprintf('%%%s%%', substr($objectManager->create('Magento\Framework\Serialize\SerializerInterface')->serialize(['attribute' => $attributeCode]), 5, -1));
        $this->addFieldToFilter('conditions_serialized', ['like' => $match]);
        return $this;
    }

    public function addCategoryFilter($cid)
    {
      $this->getSelect()
              ->where('FIND_IN_SET(?, main_table.categories_ids)', $cid);
      return $this;
    }

    public function addPriorityFilter($dir = 'ASC')
    {
      $this->getSelect()
              ->order('main_table.priority ' . $dir);
      return $this;
    }

    public function addLimit($limit = 1)
    {
      $this->getSelect()
              ->limit($limit);
      return $this;
    }

    /**
     * Find product attribute in conditions or actions
     *
     * @param string $attributeCode
     * @return $this
     * @api
     */
    public function addUrlFilter($url)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $match = sprintf('%%%s%%', substr($objectManager->create('Magento\Framework\Serialize\SerializerInterface')->serialize(['from' => $url]), 5, -1));
        $this->addFieldToFilter('url_serialized', ['like' => $match]);
        return $this;
    }

    public function addStoreFilter($store, $withAdmin = true)
    {

        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        $this->getSelect()
              ->join(
                  ['store_table' => $this->getTable('fme_restrictcustomergroup_store')],
                  'main_table.rule_id = store_table.rule_id',
                  []
              )
              ->where('store_table.store_id in (?)', [0, $store])
              ->group(
                  'main_table.rule_id'
              );


        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter([$this->_storeManager->getStore()->getId()], false);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $items = $this->getColumnValues('rule_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['cps' => $this->getTable('fme_restrictcustomergroup_store')])
                    ->where('cps.rule_id IN (?)', $items);
            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $item) {
                    $ruleId = $item->getData('rule_id');
                    if (!isset($result[$ruleId])) {
                        continue;
                    }

                    if ($result[$ruleId] == 0) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[$item->getData('rule_id')];
                        $storeCode = $this->_storeManager->getStore($storeId)->getCode();
                    }

                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', [$result[$ruleId]]);
                }
            }
        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('fme_restrictcustomergroup_store')],
                'main_table.rule_id = store_table.rule_id',
                []
            )->group(
                'main_table.rule_id'
            );
        }

        parent::_renderFiltersBefore();
    }

    /**
     * Add collection filters by identifiers
     *
     * @param mixed $ruleId
     * @param boolean $exclude
     * @return $this
     */
    public function addIdFilter($ruleId, $exclude = false)
    {

        if (is_array($ruleId)) {
            if (!empty($ruleId)) {
                if ($exclude) {
                    $condition = ['nin' => $ruleId];
                } else {
                    $condition = ['in' => $ruleId];
                }
            } else {
                $condition = '';
            }
        } else {
            if ($exclude) {
                $condition = ['neq' => $ruleId];
            } else {
                $condition = $ruleId;
            }
        }

        $this->addFieldToFilter('main_table.rule_id', $ruleId);
        return $this;
    }

    public function addCustomerGroupFilter($value)
    {

        $this->getSelect()
                ->join(
                    ['cg' => $this->getTable('fme_restrictcustomergroup_customer_group')],
                    'main_table.rule_id = cg.rule_id',
                    []
                )
                ->where('cg.customer_group_id = ?', new \Zend_Db_Expr($value));
        return $this;
    }

    public function addStaticBlockFilter($value)
    {
      $this->getSelect()
              ->join(
                  ['sb' => $this->getTable('fme_restrictcustomergroup_block')],
                  'main_table.rule_id = sb.rule_id',
                  []
              )
              ->where('sb.block_id = ?', new \Zend_Db_Expr($value));
      return $this;
    }

    public function addStatusFilter($isActive = true)
    {
      $this->getSelect()
              ->where('main_table.is_active = ? ', $isActive);
      return $this;
    }

    public function addPageFilter($ruleId)
    {
      $this->getSelect()
              ->where('FIND_IN_SET(?, main_table.cms_page_ids)', $ruleId);
      return $this;
    }
}
