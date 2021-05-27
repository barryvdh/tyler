<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml;

use Bss\AggregateCustomize\Helper\Data;
use \Magento\Downloadable\Model\Product\Type as DownloadableType;

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
     * Get allowed add options product, key is product type, value is default or not
     *
     * @return array
     */
    public function getAllowedAddProductOptions()
    {
        return ["grouped" => false, "downloadable" => true];
    }

    /**
     * Filter add product options
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = parent::_getAddProductButtonOptions();

        if (!$this->helper->isBrandManager()) {
            return $splitButtonOptions;
        }
        $allowedOptions = $this->getAllowedAddProductOptions();
        $splitButtonOptions = array_filter($splitButtonOptions, function ($btnOpt, $typeId) use ($allowedOptions) {
            return isset($allowedOptions[$typeId]);
        }, ARRAY_FILTER_USE_BOTH);

        array_walk($splitButtonOptions, function (&$option, $typeId) use ($allowedOptions) {
            if (isset($allowedOptions[$typeId])) {
                $option['default'] = $allowedOptions[$typeId];
            }
        });

        return $splitButtonOptions;
    }
}
