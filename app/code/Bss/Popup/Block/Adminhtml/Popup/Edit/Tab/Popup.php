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

namespace Bss\Popup\Block\Adminhtml\Popup\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Popup extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     *  System Store
     *
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * Yes no options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Customer Group options
     *
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    protected $customerGroupOptions;

    /**
     * Popup constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroupOptions,
        $data = []
    ) {
        $this->booleanOptions = $booleanOptions;
        $this->systemStore = $systemStore;
        $this->customerGroupOptions = $customerGroupOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Bss\Popup\Model\Popup $Popup */
        $popup = $this->_coreRegistry->registry('bss_popup_popup');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('popup_');
        $form->setFieldNameSuffix('popup');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class' => 'fieldset-wide'
            ]
        );

        if ($popup->getId()) {
            $fieldset->addField(
                'popup_id',
                'hidden',
                ['name' => 'popup_id']
            );
        }

        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Enable'),
                'title' => __('Enable'),
                'values' => $this->booleanOptions->toOptionArray(),
                'required' => true,
            ]
        )->setValue('1');

        $fieldset->addField(
            'popup_name',
            'text',
            [
                'name' => 'popup_name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'storeview',
            'multiselect',
            [
                'name' => 'storeview',
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'style' => 'height:15em;',
                'values' => $this->systemStore->getStoreValuesForForm(false, true),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'customer_group',
            'multiselect',
            [
                'name' => 'customer_group',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'style' => 'height:15em;',
                'values' => $this->customerGroupOptions->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'display_from',
            'date',
            [
                'name' => 'display_from',
                'label' => __('Start Date'),
                'class' => 'validate-date',
                'date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'time_format' => 'HH:mm:ss',
            ]
        );

        $fieldset->addField(
            'display_to',
            'date',
            [
                'name' => 'display_to',
                'label' => __('End Date'),
                'class' => 'validate-date',
                'date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'time_format' => 'HH:mm:ss',
            ]
        );

        $fieldset->addField(
            'priority',
            'text',
            [
                'name' => 'priority',
                'label' => __('Priority'),
                'title' => __('Priority'),
                'class' => 'validate-zero-or-greater',
            ]
        );

        $popupData = $this->_session->getData('bss_popup_popup_data', true);
        if ($popupData) {
            $popup->addData($popupData);
        } else {
            if (!$popup->getId()) {
                $popup->addData($popup->getDefaultValues());
            }
        }

        $form->addValues($popup->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
