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
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;

class Category extends AbstractColumn
{
    /**
     * @var Data
     */
    private $adminhtmlData;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var \Bss\AdminProductsGridwCategory\Helper\Data
     */
    protected $helper;

    /**
     * CategoryIds constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data $adminhtmlData
     * @param array $components
     * @param array $data
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param CollectionFactory $categoryCollection
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $adminhtmlData,
        ProductFactory $productFactory,
        CollectionFactory $categoryCollection,
        \Bss\AdminProductsGridwCategory\Helper\Data $helper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->adminhtmlData = $adminhtmlData;
        $this->productFactory = $productFactory;
        $this->categoryCollection = $categoryCollection;
        $this->helper = $helper;
    }

    /**
     * @param array $item
     * @return string[]
     */
    protected function _prepareItem(array &$item)
    {
        if ($this->helper->getGeneralConfig('enable')) {
            $product = $this->productFactory->create()->load($item['entity_id']);
            $categoryIds = $product->getCategoryIds();
            if (!empty($categoryIds)) {
                $categories = $this->categoryCollection->create()->setStoreId(0)
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('entity_id', $categoryIds)
                    ->addAttributeToFilter('is_active', 1);
                $arrCateIdLink = [];
                $arrCateLink = [];
                $pathConfig = $this->helper->getGeneralConfig('show_full_path');
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        $id = $category->getId();
                        $categoryName = $category->getName();
                        if ($pathConfig == 'full_path') {
                            $categoryName = $this->getFullPathName($category);
                        }

                        $url = $this->adminhtmlData->getUrl('catalog/category/edit', ['id' => $id]);
                        $arrCateIdLink[] = "<a href='" . $url . "' target='_blank'>" . $id . '</a>';
                        $arrCateLink[] = "<a href='" . $url . "' target='_blank'>" . $categoryName . '</a>';
                    }
                    $item['category_id'] = implode(', ', $arrCateIdLink);
                    $item['category'] = implode(', ', $arrCateLink);
                }
            }
        }

        return $item;
    }

    /**
     * Prepare column
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->helper->getGeneralConfig('enable')) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }

    /**
     * Get full path name by category
     * @param \Magento\Catalog\Model\Category $category
     * @return string
     */
    private function getFullPathName($category)
    {
        $fullpathName = "";
        $categories = $category->getParentCategories();
        if (empty($categories)) {
            return $category->getName();
        }
        foreach ($categories as $category) {
            $fullpathName = $fullpathName . '/' . $category->getName();
        }
        return ltrim($fullpathName, '/');
    }
}
