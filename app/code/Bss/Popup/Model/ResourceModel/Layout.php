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

class Layout extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_popup_layout', 'layout_id');
    }

    /**
     * @param $popupId
     * @param $isset
     */
    public function deleteOldLayout($popupId, $isset)
    {
        $tableName = $this->getTable('bss_popup_layout');
        if (!empty($isset)) {
            $this->getConnection()->delete(
                $tableName,
                'popup_id = '.$popupId. ' AND layout_id NOT IN ('.implode(",", $isset).')'
            );
        } else {
            $this->getConnection()->delete(
                $tableName,
                ['popup_id = ?' => $popupId]
            );
        }
    }

    /**
     * @param $popupId
     */
    public function deleteOldLayoutUpadte($popupId)
    {
        $tableName = $this->getTable('bss_popup_layout_update');
        $this->getConnection()->delete(
            $tableName,
            ['popup_id = ?' => $popupId]
        );
    }

    /**
     * @param $data
     */
    public function createNewLayoutUpadte($data)
    {
        $tableName = $this->getTable('bss_popup_layout_update');
        $this->getConnection()->insert(
            $tableName,
            $data
        );
    }
}
