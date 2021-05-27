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
 * @package    Bss_ProductGridInlineEditor
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductGridInlineEditor\Block\Adminhtml;

use Magento\Backend\Block\Template;

/**
 * Class InlineEditor
 *
 * @package Bss\ProductGridInlineEditor\Block\Adminhtml
 */
class InlineEditor extends Template
{
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $attrsetcollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $attrcollectionFactory;

    /**
     * @var \Bss\ProductGridInlineEditor\Model\Currencysymbol
     */
    protected $currencySymbol;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Bss\ProductGridInlineEditor\Helper\Data
     */
    protected $helper;

    /**
     * Custom currency symbol properties
     *
     * @var array
     */
    protected $symbolsData = [];

    /**
     * InlineEditor constructor.
     * @param Template\Context $context
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrsetcollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrcollectionFactory
     * @param \Bss\ProductGridInlineEditor\Model\Currencysymbol $currencySymbol
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Bss\ProductGridInlineEditor\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attrsetcollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attrcollectionFactory,
        \Bss\ProductGridInlineEditor\Model\CurrencySymbol $currencySymbol,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Bss\ProductGridInlineEditor\Helper\Data $helper,
        array $data = []
    ) {
        $this->attrsetcollectionFactory = $attrsetcollectionFactory;
        $this->attrcollectionFactory = $attrcollectionFactory;
        $this->currencySymbol = $currencySymbol;
        $this->eavConfig = $eavConfig;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getUrlSave()
    {
        return $this->getUrl('productgridinlineeditor/inlineEditor/save');
    }

    /**
     * @return string
     */
    public function getUrlSaveMultiples()
    {
        return $this->getUrl('productgridinlineeditor/inlineEditor/saveMultiples');
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return bool
     */
    public function isMassEdit()
    {
        return $this->helper->isMassEdit();
    }

    /**
     * @return bool
     */
    public function isSingleEditField()
    {
        return $this->helper->isSingleEditField();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttrAllowEdit()
    {
        $collectionAttributeSet = $this->attrsetcollectionFactory->create();
        $productAttrEntityTypeCode = \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE;
        $entityTypeId = $this->eavConfig->getEntityType($productAttrEntityTypeCode)->getEntityTypeId();
        // '4' is the default type ID for 'catalog_product' entity - see 'eav_entity_type' table)
        $collectionAttributeSet->setEntityTypeFilter($entityTypeId)->load();

        $attrSets = [];
        $attrsOptions = [];
        $typeAllow =  explode(',', $this->helper->getInputTypeAllow());
        if (in_array('text', $typeAllow)) {
            array_push($typeAllow, 'weight', 'qty');
        }
        if ($collectionAttributeSet->getSize() > 0) {
            foreach ($collectionAttributeSet as $item) {
                $attrSetId = $item->getAttributeSetId();
                $attrAll = $this->getCollectionAttrofAttrSet($attrSetId);
                $attrSet = [];
                foreach ($attrAll as $attr) {
                    $frontendInput = $attr->getFrontendInput();
                    if (in_array($frontendInput, $typeAllow)) {
                        $attrSet[$attr->getAttributeCode()] = [
                            'attribute_id' => $attr->getAttributeId(),
                            'attribute_code' => $attr->getAttributeCode(),
                            'frontend_input' => $frontendInput,
                            'is_required' => $attr->getIsRequired(),
                            'is_unique' => $attr->getIsUnique(),
                            'is_global' => $attr->getIsGlobal(),
                            'no_allow_type_product' => '',
                        ];

                        if ($attrSet[$attr->getAttributeCode()]['frontend_input'] == 'price') {
                            $notAllowType = 'configurable,grouped,bundle';
                            $attrSet[$attr->getAttributeCode()]['no_allow_type_product'] = $notAllowType;
                        }
                    }
                    if (!isset($attrSet['qty']) && in_array('qty', $typeAllow)) {
                        $attrSet['qty'] = [
                            'attribute_id' => '1511991',
                            'attribute_code' => 'qty',
                            'frontend_input' => 'text',
                            'is_required' => 0,
                            'is_unique' => 0,
                            'is_global' => 0,
                            'no_allow_type_product' => 'configurable,grouped,bundle',
                        ];
                    }
                    if (($frontendInput == 'boolean' || $frontendInput == 'select' || $frontendInput == 'multiselect')
                        && !isset($attrsOptions[$attr->getAttributeCode()])) {
                        $attribute = $this->eavConfig->getAttribute('catalog_product', $attr->getAttributeCode());
                        $options = $attribute->getSource()->getAllOptions();
                        $attrsOptions[$attr->getAttributeCode()] = $options;
                    }
                }
                $attrSets[$attrSetId] = $attrSet;
            }
        }

        // convert array to json
        $jsonAttrSets = $this->jsonHelper->jsonEncode($attrSets);
        $jsonAttrsOptions = $this->jsonHelper->jsonEncode($attrsOptions);

        return ['attr_sets' => $jsonAttrSets, 'attrs_options' => $jsonAttrsOptions];
    }

    /**
     * @param int $attrsetId
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    private function getCollectionAttrofAttrSet($attrsetId)
    {
        $collectionAttrofAttrSet = $this->attrcollectionFactory->create();
        $collectionAttrofAttrSet->setAttributeSetFilter($attrsetId)->addVisibleFilter()->load();
        return $collectionAttrofAttrSet;
    }

    /**
     * Returns Custom currency symbol properties
     *
     * @return string
     */
    public function getCurrencySymbolsData()
    {
        if (!$this->symbolsData) {
            $this->symbolsData = $this->currencySymbol->getCurrencySymbolsData();
        }
        return $this->jsonHelper->jsonEncode($this->symbolsData);
    }
}
