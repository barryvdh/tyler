<?php
/**
 * Class for Restrictcustomergroup Main
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
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     *
     * @var \FME\Restrictcustomergroup\Helper\Data $_restrictcustomergroupHelper
     */
    protected $_restrictcustomergroupHelper;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;
    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        Data $_restrictcustomergroupHelper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        array $data = []
    ) {

        $this->_restrictcustomergroupHelper = $_restrictcustomergroupHelper;
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
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
      $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General Settings')]);
      if ($model->getId()) {
          $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
      }
      $fieldset->addField(
          'title',
          'text',
          [
          'name' => 'title',
          'label' => __('Title'),
          'title' => __('Title'),
          'required' => true,
          'disabled' => $isElementDisabled
          ]
      );
      $fieldset->addField(
          'priority',
          'text',
          [
          'name' => 'priority',
          'label' => __('Priority'),
          'title' => __('Priority'),
          'required' => true,
          'class' => 'validate-number',
          'disabled' => $isElementDisabled,
          'style' => 'width:50px;'
          ]
      );
      /**
       * Check is single store mode
       */
      if (!$this->_storeManager->isSingleStoreMode()) {
          $field = $fieldset->addField(
              'store_ids',
              'multiselect',
              [
              'name' => 'store_ids[]',
              'label' => __('Store View'),
              'title' => __('Store View'),
              'required' => true,
              'values' => $this->_systemStore->getStoreValuesForForm(false, true),
              'disabled' => $isElementDisabled
                  ]
          );
          $renderer = $this->getLayout()->createBlock(
              'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
          );
          $field->setRenderer($renderer);
      }
      else {
          $fieldset->addField(
              'store_ids',
              'hidden',
              [
              'name' => 'store_ids[]',
              'value' => $this->_storeManager->getStore(true)->getId()
              ]
          );
          $model->setStoreIds($this->_storeManager->getStore(true)->getId());
      }
      $customerGroups = $this->_groupRepository->getList($this->_searchCriteriaBuilder->create())->getItems();
      $fieldset->addField(
          'customer_group_ids',
          'multiselect',
          [
              'name' => 'customer_group_ids[]',
              'label' => __('Customer Groups'),
              'title' => __('Customer Groups'),
              'required' => true,
              'values' => $this->_objectConverter->toOptionArray($customerGroups, 'id', 'code')
          ]
      );
      $fieldset->addField(
        'start_date',
        'date',
        [
            'name' => 'start_date',
            'label' => __('Start Date'),
            'title' => __('Start Date'),
            'date_format' => 'yyyy-MM-dd',
            'time_format' => 'hh:mm:ss'
        ]
      );
      $fieldset->addField(
        'end_date',
        'date',
        [
            'name' => 'end_date',
            'label' => __('End Date'),
            'title' => __('End Date'),
            'date_format' => 'yyyy-MM-dd',
            'time_format' => 'hh:mm:ss'
        ]
      );
      $fieldset->addField(
          'is_active',
          'select',
          [
          'label' => __('Status'),
          'title' => __('Status'),
          'name' => 'is_active',
          'required' => false,
          'options' => $model->getAvailableStatuses(),
          'disabled' => $isElementDisabled
          ]
      );
      $fieldset->addField(
          'restricted_customer_response_type',
          'select',
          [
          'label' => __('Restricted Customer Response'),
          'title' => __('Restricted Customer Response'),
          'name' => 'restricted_customer_response_type',
          'required' => false,
          'options' => $model->getAvailableModes(),
          'disabled' => $isElementDisabled
          ]
      );
      $fieldset2 = $form->addFieldset(
          'errormsg_fieldset',
          ['legend' => __('Enter Error Message')]
      );
      $fieldset2->addField(
          'error_msg',
          'editor',
          [
              'name' => 'error_msg',
              'label' => __('Error Message'),
              'title' => __('Error Message'),
              'rows' => '5',
              'cols' => '30',
              'wysiwyg' => true,
              'config' => $this->_wysiwygConfig->getConfig()
          ]
      );
      $fieldset3 = $form->addFieldset(
          'redirect_fieldset',
          ['legend' => __('Enter Redirect URL')]
      );
      $fieldset3->addField(
          'redirect_url',
          'textarea',
          [
          'name' => 'redirect_url',
          'label' => __('Redirect URL'),
          'title' => __('Redirect URL'),
          'required' => true,
          'disabled' => $isElementDisabled,
          'style' => 'width:500;'
          ]
      );
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
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('General');
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

}
