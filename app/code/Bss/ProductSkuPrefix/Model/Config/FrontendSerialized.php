<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Model\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Class FrontendSerialized
 * Frontend model for sku prefix
 */
class FrontendSerialized extends AbstractFieldArray
{
    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory
     */
    protected $productTypeFactory;

    /**
     * @var ElementFactory
     */
    protected $elementFactory;

    /**
     * Serialized constructor.
     *
     * @param Context $context
     * @param ElementFactory $elementFactory
     * @param ConfigInterface $productTypeConfig
     * @param \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory $productTypeFactory
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        ElementFactory $elementFactory,
        ConfigInterface $productTypeConfig,
        \Magento\Catalog\Api\Data\ProductTypeInterfaceFactory $productTypeFactory,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->elementFactory = $elementFactory;
        $this->productTypeConfig = $productTypeConfig;
        $this->productTypeFactory = $productTypeFactory;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        // create columns
        $this->addColumn('product_type', [
            'label' => __('Product Type'),
            'style' => 'width:300px',
        ]);
        $this->addColumn('prefix', [
            'label' => __('Prefix'),
            'style' => 'width:300px'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }

    /**
     * Render cell
     *
     * @param string $columnName
     * @return mixed|string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'product_type' && isset($this->_columns[$columnName])) {
            $options = $this->getAllProductTypes();

            $element = $this->elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            )->setStyle('width:150px');

            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * Get all product types
     *
     * @return array
     */
    protected function getAllProductTypes()
    {
        $productTypes = [];
        foreach ($this->productTypeConfig->getAll() as $productTypeData) {
            $productType = [
                'value' => $productTypeData['name'],
                'label' => $productTypeData['label']
            ];
            $productTypes[] = $productType;
        }

        return $productTypes;
    }
}
