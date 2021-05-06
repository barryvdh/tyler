<?php
/**
 * Class for Restrictcustomergroup Conditions
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use \FME\Restrictcustomergroup\Helper\Data;

class Conditions extends Generic implements TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {

        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __('Products');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __('Products');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
      $model = $this->_coreRegistry->registry('restrictcustomergroup_data');
      /** @var \Magento\Framework\Data\Form $form */
      $form = $this->_formFactory->create();
      $form->setHtmlIdPrefix('rule_');
      $renderer = $this->_rendererFieldset->setTemplate(
          'Magento_CatalogRule::promo/fieldset.phtml'
      )->setNewChildUrl(
          $this->getUrl('restrictcustomergroup/rule/newConditionHtml/form/rule_conditions_fieldset')
      );
      $fieldset = $form->addFieldset(
          'conditions_fieldset',
          ['legend' => __('Please select rules for product')]
      )->setRenderer(
          $renderer
      );
      $fieldset->addField(
          'conditions',
          'text',
          ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'required' => true]
      )->setRule(
          $model
      )->setRenderer(
          $this->_conditions
      );
      $form->setValues($model->getData());
      $this->setForm($form);
      return parent::_prepareForm();
    }
}
