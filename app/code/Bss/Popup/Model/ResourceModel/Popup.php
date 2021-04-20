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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Model\ResourceModel;

class Popup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        $this->dateTime = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_popup_popup', 'popup_id');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPopupByDate()
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('status = 1');
        return $adapter->fetchAssoc($select);
    }

    /**
     * Before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Bss\Popup\Model\Popup $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->dateTime->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateTime->date());
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param $handleList
     * @return array
     */
    public function getPopupByHandleList($handleList)
    {
        $select = $this->getSelect($handleList);
        $result = $this->getConnection()->fetchAll($select);
        return $result;
    }

    /**
     * @param $handleList
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect($handleList)
    {
        $handleFilter = "t1.handle = 'default'";
        foreach ($handleList as $handle) {
            $handleFilter .= " OR t1.handle = '" . $handle . "'";
        }
        $select = $this->getConnection()->select()->from(
            ['t1' => $this->getTable('bss_popup_layout_update')],
            []
        )->joinLeft(
            ['t2' => $this->getTable('bss_popup_popup')],
            't1.popup_id = t2.popup_id',
            ['t2.popup_id', 't2.storeview', 't2.customer_group', 't2.display_from', 't2.display_to']
        )->joinLeft(
            ['t3' => $this->getTable('bss_popup_layout')],
            't1.layout_id = t3.layout_id',
            ['t3.page_group', 't3.entities']
        )->where(
            't2.status = 1 AND ( '.$handleFilter.' )'
        )->order('t2.priority ASC');

        return $select;
    }
}