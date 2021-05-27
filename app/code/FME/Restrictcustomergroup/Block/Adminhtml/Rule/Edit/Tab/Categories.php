<?php
/**
 * Class for Restrictcustomergroup Categories
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Categories extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param array $data
     */
    protected $_systemStore;
    protected $_groupRepository;
    protected $_searchCriteriaBuilder;
    protected $_objectConverter;
    protected $_categoryHelper;
    protected $_categorylist;

    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Store\Model\System\Store $systemStore,
        FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \FME\Restrictcustomergroup\Model\Rule\Source\ListCategories $categorylist,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_categorylist = $categorylist;
        parent::__construct($context, $registry, $formFactory, $data);
    }


    /**
     * Prepare form before rendering HTML
     *
     * @return Generic
     */
    protected function _prepareForm()
    {
      /* @var $model \FME\Restrictcustomergroup\Model\Rule */
      $model = $this->_coreRegistry->registry('restrictcustomergroup_data');
      $isElementDisabled = false;


      /** @var \Magento\Framework\Data\Form $form */
      //$form = $this->_formFactory->create();
      $form = $this->_formFactory->create(
          [
              'data' => [
                 'id' => 'edit_form',
                 'action' => $this->getData('action'),
                 'method' => 'post',
                 'enctype' => 'multipart/form-data'
             ]
         ]);
        $form->setHtmlIdPrefix('rule_');
        $fieldset = $form->addFieldset('categories_fieldset', array(
            'legend'    => __('Select Categories'),
            'class'     => 'fieldset-wide',
        ));
        if ($model->getId()) {
            $fieldset->addField('template_id', 'hidden', ['name' => 'template_id']);
        }
        $field = $fieldset->addField(
                'categories_ids',
                'multiselect',
                [
                'name' => 'categories_ids',
                'label' => __('Categories'),
                'title' => __('Categories'),
                'required' => false,
                'values' => $this->_categorylist->toOptionArray(),
                'disabled' => $isElementDisabled
                    ]
        );
        $form->setValues($model->getData());
        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }
        $this->setForm($form);
        $this->_eventManager->dispatch('adminhtml_rule_edit_tab_categories_prepare_form', ['form' => $form]);
        return parent::_prepareForm();
    }


    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Categories');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Categories');
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
