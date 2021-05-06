<?php
/**
 * Password Protected
 *
 * @category    Addify
 * @package     Addify_PasswordProtected
 * @author      Addify
 * @Email       addifypro@gmail.com
 *
 */
namespace Addify\PasswordProtected\Block\Adminhtml\ProductGrid\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @param Visibility|null $visibility
     * @param Status|null $status
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Addify\PasswordProtected\Helper\HelperData $HelperData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Registry $registry,
        array $data = [],
        Visibility $visibility = null,
        Status $status = null
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_HelperData = $HelperData;
        $this->registry = $registry;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        $this->status = $status ?: ObjectManager::getInstance()->get(Status::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tab_related_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {

        if ($column->getId() == 'in_tab_related_products')
        {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
        }
        else
        {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'visibility'
        )->addAttributeToSelect(
            'status'
        )->addAttributeToSelect(
            'price'
        )->setOrder('entity_id','ASC');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->addStoreFilter($storeId);
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {   
        if ($this->getRequest()->getParam('id')) {
            $this->setDefaultFilter(['in_tab_related_products' => 1]);
        }

            $this->addColumn(
                'in_tab_related_products',
                [
                    'type' => 'checkbox',
                    'name' => 'in_tab_related_products',
                    'values' => $this->_getSelectedProducts(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku']);
        $this->addColumn(
            'visibility',
            [
                'header' => __('Visibility'),
                'index' => 'visibility',
                'type' => 'options',
                'options' => $this->visibility->getOptionArray(),
                'header_css_class' => 'col-visibility',
                'column_css_class' => 'col-visibility'
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray()
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ),
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => 'false',
                'column_css_class'=>'no-display',//this sets a css class to the column row item
                'header_css_class'=>'no-display',//this sets a css class to the column header

            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        
        return $this->getUrl('passwordprotected/product/productgrid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {   
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) 
        {
            $products = $this->_HelperData->getRelatedProductArray($this->getRequest()->getParam('id'));

        }

        return $products;
    }
}
