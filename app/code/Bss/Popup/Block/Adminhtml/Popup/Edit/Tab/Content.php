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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Block\Adminhtml\Popup\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class Content extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Bss\Popup\Model\Source\Template
     */
    protected $template;
    /**
     * Wysiwyg Config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * Content constructor.
     *
     * @param \Bss\Popup\Model\Source\Template $template
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Bss\Popup\Model\Source\Template $template,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->template=$template;
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Form
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Bss\Popup\Model\Popup $popup */
        $popup = $this->_coreRegistry->registry('bss_popup_popup');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('popup_');
        $form->setFieldNameSuffix('popup');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Content and Design'),
                'class'  => 'fieldset-wide'
            ]
        );

        $config['document_base_url'] = $this->getData('store_media_url');
        $config['add_variables'] = true;
        $config['add_widgets'] = true;
        $config['add_directives'] = true;
        $config['use_container'] = true;
        $config['container_class'] = 'hor-scroll';

        $fieldset->addField(
            'type_template',
            'select',
            [
                'name' => 'type_template',
                'label' => __('Template'),
                'title' => __('Template'),
                'values' => $this->template->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'load_template',
            'button',
            [
                'name' => 'load_template',
                'value'=>__('Load Template')
            ]
        );
        $fieldset->addField(
            'popup_content',
            'editor',
            [
                'name'  => 'popup_content',
                'label' => __('Content'),
                'title' => __('Content'),
                'wysiwyg'   => true,
                'required' => true,
                'force_load' => true,
                'config' => $this->wysiwygConfig->getConfig($config)
            ]
        );

        $fieldset->addField(
            'popup_css',
            'textarea',
            [
                'name' => 'popup_css',
                'class' => 'jscolor {hash:true,refine:false}',
                'label' => __('Pop-up CSS'),
                'style' => 'height:5em;',
                'title' => __('Pop-up CSS'),
                'note' => __("CSS change popup design. If blank, default will be used.")
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
        return __('Content and Design');
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
