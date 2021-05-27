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

namespace Bss\Popup\Block\Adminhtml\Popup;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Bss\Popup\Helper\Data
     */
    protected $dataHelper;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Bss\Popup\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        \Bss\Popup\Helper\Data $dataHelper,
        $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Popup edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'popup_id';
        $this->_blockGroup = 'Bss_Popup';
        $this->_controller = 'adminhtml_popup';
        parent::_construct();
        $this->buttonList->update('delete', 'label', __('Delete'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );

        $this->buttonList->add(
            'preview-popup',
            [
                'label' => __('Preview'),
                'class' => 'preview-popup'
            ]
        );
        $this->buttonList->update('save', 'label', __('Save Pop-up'));
    }

    /**
     * @inheritDoc
     */
    public function getFormScripts()
    {
        /**
         * Get url when open preview popup
         */
        $urlPreview = $this->dataHelper->getUrlPreview();
        $newScript = ['require([
        \'jquery\',
        \'mage/backend/form\',
        \'mage/backend/validation\'
    ], function($){
    \'use strict\';
        var btnPreview = $(\'#preview-popup\'),
        editForm = $(\'#edit_form\');
        btnPreview.click(function (e) {
            $(\'#togglepopup_popup_content\').click();
            $(\'#togglepopup_popup_content\').click();
            if (editForm.validation(\'isValid\')) {
                var newForm = editForm.clone();
                editForm.find(\'input\').each(function () {
                    newForm.find(\'#\' +$(this).attr(\'id\')+ \'\').val($(this).val());
                });
                editForm.find(\'select\').each(function () {
                    newForm.find(\'#\' +$(this).attr(\'id\')+ \'\').val($(this).val());
                });
                editForm.find(\'textarea\').each(function () {
                    newForm.find(\'#\' +$(this).attr(\'id\')+ \'\').val($(this).val());
                });
                var rId = Math.random().toString(36).substring(7);
                newForm.attr(\'action\', \'' .$urlPreview. '\');
                newForm.attr(\'id\', rId);
                newForm.attr(\'target\', \'_blank\');
                newForm.appendTo($(\'body\')).submit();
                newForm.detach();
            }
        });
    });'];
        $this->_formScripts = array_merge($this->_formScripts, $newScript);
        return parent::getFormScripts();
    }

    /**
     * Retrieve text for header element depending on loaded Popup
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Bss\Popup\Model\Popup $Popup */
        $popup = $this->coreRegistry->registry('bss_popup_popup');
        if ($popup->getId()) {
            return __("");
        }
        return __('New Pop-up');
    }
}
