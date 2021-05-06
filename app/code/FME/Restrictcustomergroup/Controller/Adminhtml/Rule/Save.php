<?php
/**
 * Class for Restrictcustomergroup Save
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

class Save extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('FME_Restrictcustomergroup::save');
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data)
        {
            $model = $this->_objectManager->create('FME\Restrictcustomergroup\Model\Rule');

            $id = $this->getRequest()->getParam('rule_id');

            if ($id)
            {
              $model->load($id);
            }

            if (isset($data['cms_page_ids']))
            {
              $data['cms_page_ids'] = implode(',', $data['cms_page_ids']);
            }
            else
            {
              $data['cms_page_ids'] = '';
            }

            if (array_key_exists('categories_ids', $data))
            {
              $is_select_categories = $data['categories_ids'];
              $is_select_categories = implode(",", $is_select_categories);
              $data['categories_ids'] = $is_select_categories;
            }

            if (isset($data['rule']))
            {
              $data['conditions'] = $data['rule']['conditions'];
              unset($data['rule']);
            }

            if (isset($data['url']) && count($data['url']) > 0)
            {
              $data['url_serialized'] = $this->_objectManager->create('Magento\Framework\Serialize\SerializerInterface')->serialize($data['url']);
              unset($data['url']);
            }

            $model->loadPost($data);

            $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($model->getData());

            try
            {
                $model->save();
                $this->messageManager->addSuccess(__('Rule saved successfully.'));
                $this->_getSession()->setFormData(false);
                //$this->_objectManager->get('Magento\Framework\Registry')->unregister('restrictcustomergroup_data');

                // old rule
                if (array_key_exists('rule_id', $data))
                {
                  if (array_key_exists("categories_ids", $data))
                  {
                    // pass
                  }
                  else
                  {
                    $this->emptyCategoriesColumn($data['rule_id']);
                  }
                }

                // old rule
                if (array_key_exists('rule_id', $data))
                {
                  if (array_key_exists("categories_ids", $data))
                  {
                    $this->deletePreviousCategories($data['rule_id']);
                    // means user selected some categories
                    $this->insertIntoCategorytable($data['rule_id'], $data['categories_ids']);
                  }
                }
                // new rule
                else
                {
                  $lastInsertedRuleId = $this->getLastInsertedRuleId();
                  if (array_key_exists("categories_ids", $data))
                  {
                    // means user selected some categories
                    $this->insertIntoCategorytable($lastInsertedRuleId, $data['categories_ids']);
                  }
                }

                if ($this->getRequest()->getParam('back'))
                {
                  return $resultRedirect->setPath('*/*/edit', ['rule_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            }
            catch (\Magento\Framework\Exception\LocalizedException $e)
            {
              $this->messageManager->addError($e->getMessage());
            }
            catch (\RuntimeException $e)
            {
              $this->messageManager->addError($e->getMessage());
            }
            catch (\Exception $e)
            {
              $this->messageManager->addException($e, __('Something went wrong while saving the rule.'));
            }
            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['rule_id' => $this->getRequest()->getParam('rule_id')]);
        }
        //$this->_objectManager->get('Magento\Framework\Registry')->unregister('restrictcustomergroup_data');
        return $resultRedirect->setPath('*/*/');
    }

    public function insertIntoCategorytable($ruleId, $categoryIds)
    {
      if (strpos($categoryIds, ',') !== false)
      {
        $categoryIds = explode(',', $categoryIds);
      }
      else
      {
        $temp = [];
        $temp[0] = $categoryIds;
        $categoryIds = $temp;
      }
      foreach ($categoryIds as $key => $categoryId)
      {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coreResource = $objectManager->create('Magento\Framework\App\ResourceConnection');
        $categoryTable = $coreResource->getTableName('fme_restrictcustomergroup_category');
    		$categorySql = "Insert Into " . $categoryTable . " (rule_id, category_id) Values ('$ruleId','$categoryId')";
    		$connection = $coreResource->getConnection('core_write');
    		$result = $connection->query($categorySql);
      }
    }

    public function getLastInsertedRuleId()
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $coreResource = $objectManager->create('Magento\Framework\App\ResourceConnection');
      $ruleTable = $coreResource->getTableName('fme_restrictcustomergroup_rule');
  		$ruleSql = "Select * from ".$ruleTable. " Order By rule_id DESC LIMIT 1";
  		$connection = $coreResource->getConnection('core_read');
  		$result = $connection->fetchAll($ruleSql);
  		return $result[0]['rule_id'];
    }

    public function deletePreviousCategories($ruleId)
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $coreResource = $objectManager->create('Magento\Framework\App\ResourceConnection');
      $categoryTable = $coreResource->getTableName('fme_restrictcustomergroup_category');
      $categorySql = "Delete from " . $categoryTable . " where rule_id = ".$ruleId;
      $connection = $coreResource->getConnection('core_write');
      $result = $connection->query($categorySql);
    }

    public function emptyCategoriesColumn($ruleId)
    {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $coreResource = $objectManager->create('Magento\Framework\App\ResourceConnection');
      $ruleTable = $coreResource->getTableName('fme_restrictcustomergroup_rule');
      $ruleSql = "Update " . $ruleTable . " set categories_ids = '' where rule_id = ".$ruleId;
      $connection = $coreResource->getConnection('core_write');
      $result = $connection->query($ruleSql);

    }
}
































/*
asd
*/
