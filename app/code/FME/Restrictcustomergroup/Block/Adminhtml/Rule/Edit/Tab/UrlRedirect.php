<?php
/**
 * Class for Restrictcustomergroup UrlRedirect
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit\Tab;

use \FME\Restrictcustomergroup\Helper\Data;

/**
 * FME Restrictcustomergroup index edit form main tab
 */
class UrlRedirect extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     *
     * @var \FME\Restrictcustomergroup\Helper\Data $_restrictcustomergroupHelper
     */
    protected $_restrictcustomergroupHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        Data $_restrictcustomergroupHelper,
        array $data = []
    ) {

        $this->_restrictcustomergroupHelper = $_restrictcustomergroupHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
      /* @var $model \FME\Restrictcustomergroup\Model\Rule */
      $model = $this->_coreRegistry->registry('restrictcustomergroup_data');
      /*
       * Checking if user have permissions to save information
       */
      if ($this->_isAllowedAction('FME_Restrictcustomergroup::save')) {
          $isElementDisabled = false;
      } else {
          $isElementDisabled = true;
      }
      /** @var \Magento\Framework\Data\Form $form */
      $form = $this->_formFactory->create();
      $form->setHtmlIdPrefix('rule_');
      $fieldset = $form->addFieldset(
          'base_fieldset',
          ['legend' => __('Provide URL(s) to redirect from store')]
      );
      if ($model->getId()) {
          $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
      }
      $field = $fieldset->addField(
          'assignurl',
          'text',
          [
          'name' => 'assignurl',
          'label' => __('Assign URL'),
          'title' => __('Assign URL'),
          'required' => false,
          'disabled' => $isElementDisabled
          ]
      );
      $renderer = $this->getLayout()
              ->createBlock('FME\Restrictcustomergroup\Block\Adminhtml\Rule\Renderer\Manual\Assignurl');
      $field->setRenderer($renderer);
      if (!$model->getId()) {
          $model->setData('is_active', $isElementDisabled ? '0' : '1');
      }
      $form->setValues($model->getData());
      $this->setForm($form);
      return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('URL Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('URL Conditions');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function getRuleType($model)
    {
        return $this->_restrictcustomergroupHelper->getRuleType($model);
    }
}
