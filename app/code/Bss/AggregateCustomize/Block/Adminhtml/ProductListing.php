<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml;

/**
 * Class ProductListing
 * Remove dropdown in add product button
 */
class ProductListing extends \Magento\Catalog\Block\Adminhtml\Product
{
    /**
     * Remove dropdown and have it so you just click ‘Add Product’ and it defaults to creating a Downloadable Product
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->buttonList->update(
            "add_new",
            "class_name",
            \Magento\Backend\Block\Widget\Button::class
        );
        $this->buttonList->update(
            "add_new",
            "class",
            "action-primary"
        );
        // Add create downloadable product action for 'add product' button
        $this->buttonList->update(
            "add_new",
            "onclick",
            "setLocation('" . $this->_getProductCreateUrl("downloadable") . "')"
        );
        return $this;
    }
}
