<?php
namespace Bss\BrandRepresentative\Block\Adminhtml\Form\Renderer\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class BrandsTree extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = "Bss_BrandRepresentative::featured_brands_field.phtml";

    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $categoriesTree;

    /**
     * BrandsTree constructor.
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        SerializerInterface $serializer,
        Context $context,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->serializer = $serializer;
        parent::__construct($context, $data, $secureRenderer);
    }

    /**
     * Get field html content
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setData('featured_brands_input_name', $element->getName());
        $this->setData('featured_brands_html_id', $element->getHtmlId());
        $this->processSelectedBrands($element);
        return $this->_toHtml();
    }

    /**
     * Set value of the field
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     */
    protected function processSelectedBrands(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $brands = [];

        if ($element->getEscapedValue()) {
            $brands = explode(",", $element->getEscapedValue());
        }

        $this->setData('selected_brands', $this->serializer->serialize($brands));
    }

    /**
     * Serialize categories tree data to js component
     *
     * @return bool|string
     */
    public function getSerializedCategories()
    {
        try {
            return $this->serializer->serialize($this->getCategoriesTree());
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return "[]";
        }
    }

    /**
     * Retrieve categories tree
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesTree()
    {
        if ($this->categoriesTree === null) {
            $storeId = $this->_request->getParam('store');
            /* @var $matchingNamesCollection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $matchingNamesCollection = $this->categoryCollectionFactory->create();

            $matchingNamesCollection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['neq' => CategoryModel::TREE_ROOT_ID])
                ->setStoreId($storeId);

            $shownCategoriesIds = [];

            /** @var \Magento\Catalog\Model\Category $category */
            foreach ($matchingNamesCollection as $category) {
                foreach (explode('/', $category->getPath()) as $parentId) {
                    $shownCategoriesIds[$parentId] = 1;
                }
            }

            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            $collection = $this->categoryCollectionFactory->create();

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($shownCategoriesIds)])
                ->addAttributeToSelect(['name', 'is_active', 'parent_id'])
                ->setStoreId($storeId);

            $categoryById = [
                CategoryModel::TREE_ROOT_ID => [
                    'value' => CategoryModel::TREE_ROOT_ID
                ],
            ];

            foreach ($collection as $category) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId];
                    }
                }

                $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
                $categoryById[$category->getId()]['label'] = $category->getName();
                $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
            }

            $this->categoriesTree = $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
        }

        return $this->categoriesTree;
    }
}
