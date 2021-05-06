<?php
/**
 * Class for Restrictcustomergroup Block Product Widget NewWidget
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Product\Widget;

class NewWidget extends \Magento\Catalog\Block\Product\Widget\NewWidget
{
    /**
     * @return void
     */
    protected function _prepareLayout()
    {
      $this->setTemplate('Magento_Catalog::' . $this->getTemplate());
    }

    /**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    protected function _getProductCollection()
    {
        switch ($this->getDisplayType())
        {
            case self::DISPLAY_TYPE_NEW_PRODUCTS:
                $collection = parent::_getProductCollection()
                    ->setPageSize($this->getPageSize())
                    ->setCurPage($this->getCurrentPage());
                break;
            default:
                $collection = $this->_getRecentlyAddedProductsCollection();
                break;
        }
        if (!empty($collection->getData()))
        {
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $helper = $objectManager->create('FME\Restrictcustomergroup\Helper\Data');
          $date = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
          $storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');
          $restrictcustomergroupfactory = $objectManager->create('FME\Restrictcustomergroup\Model\RuleFactory')->create();
          if (!$helper->isEnabledInFrontend())
          {
            return $collection;
          }
          $restrictcollection = $restrictcustomergroupfactory->getCollection()
                    ->addStoreFilter([$storeManager->getStore()->getId()], false)
                    ->addCustomerGroupFilter($helper->getCustomerGroupId())
                    ->addStatusFilter();
          if (empty($restrictcollection))
          {
            return $collection;
          }
          foreach ($collection as $key => $product)
          {
            $ruleProducts = [];
            foreach ($restrictcollection as $item)
            {
              if (strpos($item->getData('conditions_serialized'), '"conditions"') !== false)
              {
                $rule = $restrictcustomergroupfactory->load($item->getId());
                if ($rule->getConditions()->validate($product))
                {
                  $ruleProducts[$item->getId()] = $product->getEntityId();
                }
              }
            }
            $allRules = array_keys($ruleProducts);
            if (empty($allRules))
            {
              continue;
            }
            $restrictcollection->addIdFilter($allRules)
                       ->addPriorityFilter()
                       ->addLimit();
            if (!empty($restrictcollection->getData()))
            {
               foreach ($restrictcollection->getData() as $item)
               {
                 if(empty($item['start_date']) || empty($item['end_date']))
                 {
                   $collection->removeItemByKey($product->getEntityId());
                 }
                 else
                 {
                   $currentDate = $this->date->gmtDate();
                   if (($currentDate >= $item['start_date']) && ($currentDate <= $item['end_date']))
                   {
                     $collection->removeItemByKey($product->getEntityId());
                   }
                 }
               }
            }
          }
        }
        return $collection;
    }
}
