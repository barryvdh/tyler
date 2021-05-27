<?php
/**
 * Class for Restrictcustomergroup Blocks
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Controller\Adminhtml\Rule;

class Blocks extends \Magento\Backend\App\Action
{
    protected $_resultLayoutFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\LayoutFactory $layoutFactory
    ) {
      $this->_resultLayoutFactory = $layoutFactory;
      parent::__construct($context);
    }

    public function execute()
    {
      $resultLayout = $this->_resultLayoutFactory->create();
      $this->_initRuleblocks();
      $resultLayout->getLayout()
              ->getBlock('restrictcustomergroup.edit.tab.blocks')
              ->setRelatedBlocks($this->getRequest()->getPost('related_blocks', null));
      return $resultLayout;
    }

    protected function _initRuleBlocks()
    {
      $rule = $this->_objectManager->create('FME\Restrictcustomergroup\Model\Rule');
      $ruleId = (int) $this->getRequest()->getParam('rule_id');
      if ($ruleId) {
          $rule->load($ruleId);
      }
      $this->_objectManager->get('Magento\Framework\Registry')
              ->register('current_restrictcustomergroup_blocks', $rule);
      return $rule;
    }
}
