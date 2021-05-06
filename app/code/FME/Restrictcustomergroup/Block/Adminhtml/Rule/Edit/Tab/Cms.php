<?php
/**
 * Class for Restrictcustomergroup Cms
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit\Tab;

use \FME\Restrictcustomergroup\Helper\Data;

class Cms extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
      $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Select CMS pages to apply rule')]);
      if ($model->getId()) {
          $fieldset->addField('rule_id', 'hidden', ['name' => 'rule_id']);
      }
      $pageValues = $this->_restrictcustomergroupHelper->getCmsPageModel()
              ->getCollection()
              ->addFieldToFilter('is_active', 1)
              ->toOptionIdArray();
      $fieldset->addField(
          'cms_page_ids',
          'multiselect',
          [
          'label' => __('CMS Page'),
          'title' => __('CMS Page'),
          'name' => 'cms_page_ids',
          'required' => false,
          'values' => $pageValues,
          'disabled' => $isElementDisabled
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
        return __('CMS Pages');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('CMS Pages');
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
