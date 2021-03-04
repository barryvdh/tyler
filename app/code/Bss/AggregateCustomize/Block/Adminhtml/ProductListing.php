<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml;

use Bss\AggregateCustomize\Helper\Data;

/**
 * Class ProductListing
 * Remove dropdown in add product button
 */
class ProductListing extends \Magento\Catalog\Block\Adminhtml\Product
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ProductListing constructor.
     *
     * @param Data $helper
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Product\TypeFactory $typeFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Data $helper,
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $typeFactory, $productFactory, $data);
    }

    /**
     * Remove dropdown and have it so you just click ‘Add Product’ and it defaults to creating a Downloadable Product
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->helper->isBrandManager()) {
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
        }
        return $this;
    }
}
