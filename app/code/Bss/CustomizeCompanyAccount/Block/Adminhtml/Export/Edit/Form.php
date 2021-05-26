<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Block\Adminhtml\Export\Edit;

/**
 * Class Form
 * Add export sub-user checkbox to form
 */
class Form extends \Magento\ImportExport\Block\Adminhtml\Export\Edit\Form
{
    /**
     * Add export sub-user checkbox
     *
     * @return Form
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->getElement("base_fieldset");
        if ($fieldset instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
            $fieldset->addField(
                'export_sub_user',
                'checkbox',
                [
                    'name' => 'export_sub_user',
                    'label' => __('Export Sub-user'),
                    'title' => __('Export Sub-user'),
                    'value' => 1,
                    'css_class' => 'hide export-sub-user-container',
                    'onchange' => 'varienExport.getSubUserFilter(this);'
                ]
            );
        }

        return $this;
    }
}
