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

use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Layout
 * @package Bss\Popup\Block\Adminhtml\Popup\Edit\Tab
 */
class Layout extends \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout
{
    /**
     * @var string
     */
    protected $_template = 'Bss_Popup::layout.phtml';

    /**
     * @var \Bss\Popup\Model\ResourceModel\Layout\Collection
     */
    protected $layoutCollection;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var \Bss\Popup\Helper\Compatible
     */
    protected $compatible;

    /**
     * Layout constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Bss\Popup\Model\ResourceModel\Layout\Collection $layoutCollection
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     * @param Json|null $serializer
     * @codingStandardsIgnoreStart
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Product\Type $productType,
        \Bss\Popup\Model\ResourceModel\Layout\Collection $layoutCollection,
        //\Magento\Framework\App\RequestInterface $request,
        Json $serializer,
        \Bss\Popup\Helper\Compatible $compatible,
        array $data = []
    ) {
        $this->layoutCollection = $layoutCollection;
        $this->serializer = $serializer;
        $this->compatible = $compatible;
        parent::__construct($context, $productType, $data);
    }
    // @codingStandardsIgnoreEnd

    /**
     * @param string $string
     * @param bool $escapeSingleQuote
     * @return string
     */
    public function escapeHtmlAttr($string, $escapeSingleQuote = true)
    {
        return $this->compatible->escapeHtmlAttr($string, $escapeSingleQuote);
    }

    /**
     * @param string $string
     * @return string
     */
    public function escapeJs($string)
    {
        return $this->compatible->escapeJs($string);
    }

    /**
     * Generate url to get categories chooser by ajax query
     *
     * @return string
     */
    public function getCategoriesChooserUrl()
    {
        return $this->getUrl('adminhtml/widget_instance/categories', ['_current' => true]);
    }

    /**
     * Generate url to get products chooser by ajax query
     *
     * @return string
     */
    public function getProductsChooserUrl()
    {
        return $this->getUrl('adminhtml/widget_instance/products', ['_current' => true]);
    }

    /**
     * Generate url to get reference block chooser by ajax query
     *
     * @return string
     */
    public function getBlockChooserUrl()
    {
        return $this->getUrl('adminhtml/widget_instance/blocks', ['_current' => true]);
    }

    /**
     * Retrieve Display On options array.
     * - Categories (anchor and not anchor)
     * - Products (product types depend on configuration)
     * - Generic (predefined) pages (all pages and single layout update)
     *
     * @return array
     */
    protected function _getDisplayOnOptions()
    {
        $options = [];
        $options[] = ['value' => '', 'label' => $this->escapeHtmlAttr(__('-- Please Select --'))];
        $options[] = [
            'label' => __('Categories'),
            'value' => [
                ['value' => 'anchor_categories', 'label' => $this->escapeHtmlAttr(__('Anchor Categories'))],
                ['value' => 'notanchor_categories', 'label' => $this->escapeHtmlAttr(__('Non-Anchor Categories'))],
            ],
        ];
        foreach ($this->_productType->getTypes() as $typeId => $type) {
            $productsOptions[] = [
                'value' => $typeId . '_products',
                'label' => $this->escapeHtmlAttr($type['label']),
            ];
        }
        array_unshift(
            $productsOptions,
            ['value' => 'all_products', 'label' => $this->escapeHtmlAttr(__('All Product Types'))]
        );
        $options[] = ['label' => $this->escapeHtmlAttr(__('Products')), 'value' => $productsOptions];
        $options[] = [
            'label' => $this->escapeHtmlAttr(__('Generic Pages')),
            'value' => [
                ['value' => 'all_pages', 'label' => $this->escapeHtmlAttr(__('All Pages'))],
                ['value' => 'pages', 'label' => $this->escapeHtmlAttr(__('Specified Page'))]
            ],
        ];
        return $options;
    }

    /**
     * Generate url to get template chooser by ajax query
     *
     * @return string
     */
    public function getTemplateChooserUrl()
    {
        return $this->getUrl('adminhtml/widget_instance/template', ['_current' => true]);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLayoutsChooser()
    {
        $chooserBlock = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout::class
        )->setName(
            'widget_instance[<%- data.id %>][pages][layout_handle]'
        )->setId(
            'layout_handle'
        )->setClass(
            'required-entry select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
            "this.up(\'div.pages\'), this.value)\""
        )->setArea(
            'frontend'
        )->setTheme(
            '2'
        );
        return $chooserBlock->toHtml();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLayoutsChooserMultiple()
    {
        $chooserBlock = $this->getLayout()->createBlock(
            \Bss\Popup\Block\Adminhtml\Chooser\Layout::class
        )->setName(
            'widget_instance[<%- data.id %>][all_pages][entities][]'
        )->setId(
            'layout_handle'
        )->setClass(
            'select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
            "this.up(\'div.pages\'), this.value)\"" .
            " multiple" .
            " style=\"overflow-y:scroll;height:100%\""
        )->setArea(
            'frontend'
        )->setTheme(
            '2'
        );
        return $chooserBlock->toHtml();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPageLayoutsPageChooser()
    {
        $chooserBlock = $this->getLayout()->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\DesignAbstraction::class
        )->setName(
            'widget_instance[<%- data.id %>][page_layouts][layout_handle]'
        )->setId(
            'layout_handle'
        )->setClass(
            'required-entry select'
        )->setExtraParams(
            "onchange=\"WidgetInstance.loadSelectBoxByType(\'block_reference\', " .
            "this.up(\'div.pages\'), this.value)\""
        )->setArea(
            'frontend'
        )->setTheme(
            '2'
        );
        return $chooserBlock->toHtml();
    }

    /**
     * Prepare and retrieve page groups data of widget instance
     *
     * @return array
     */
    public function getPageGroups()
    {
        $pageGroups = [];
        $popupId = $this->_request->getParam('popup_id');
        if (!$popupId || $popupId == '') {
            return [];
        }
        $model = $this->layoutCollection->addFieldToFilter('popup_id', $popupId);
        foreach ($model as $row) {
            $pageGroup = $row->getData();
            $pageGroups[] = $this->serializer->serialize($this->getPageGroup($pageGroup));
        }
        return $pageGroups;
    }

    /**
     * @param array $pageGroup
     * @return array
     */
    private function getPageGroup(array $pageGroup)
    {
        return [
            'page_id' => $pageGroup['layout_id'],
            'group' => $pageGroup['page_group'],
            'block' => '',
            'for_value' => $pageGroup['page_for'],
            'layout_handle' => $pageGroup['layout_handle'],
            $pageGroup['page_group'] . '_entities' => $pageGroup['entities'],
            'template' => '',
        ];
    }
}
