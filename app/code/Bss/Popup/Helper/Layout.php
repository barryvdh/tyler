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

namespace Bss\Popup\Helper;

class Layout extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Popup\Model\ResourceModel\Layout
     */
    protected $layoutResourceModel;

    /**
     * @var \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory
     */
    protected $popupCollection;

    /**
     * @var \Bss\Popup\Model\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $productType;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $categoryModel;

    /**
     * Layout constructor.
     * @param \Bss\Popup\Model\ResourceModel\Layout $layoutesourceModel
     * @param \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory $popupCollection
     * @param \Bss\Popup\Model\LayoutFactory $layoutFactory
     * @param \Magento\Catalog\Model\Product $productModel
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Catalog\Model\Category $categoryModel
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Bss\Popup\Model\ResourceModel\Layout $layoutResourceModel,
        \Bss\Popup\Model\ResourceModel\Popup\CollectionFactory $popupCollection,
        \Bss\Popup\Model\LayoutFactory $layoutFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\Category $categoryModel,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->layoutResourceModel = $layoutResourceModel;
        $this->popupCollection = $popupCollection;
        $this->layoutFactory = $layoutFactory;
        $this->productModel = $productModel;
        $this->productType = $productType;
        $this->categoryModel = $categoryModel;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function getPopupCollection()
    {
        $collection = $this->popupCollection->create();
        return $collection;
    }

    /**
     * @param $excludeProduct
     * @return string
     */
    public function getAllExcludeProductId($excludeProduct)
    {
        $productIds = $this->productModel->getCollection()->addAttributeToFilter('entity_id', ['nin' => $excludeProduct])->getAllIds();
        if (!empty($productIds)) {
            $productIds = implode(",", $productIds);
            return $productIds;
        }
        return '';
    }

    /**
     * @param $ids
     * @return array
     */
    public function checkCategoryExclude($ids)
    {
        $notAnchorCategoryIds = $this->categoryModel->getCollection()->addAttributeToFilter('entity_id', ['in' => $ids])->addAttributeToFilter('is_anchor', 0)->getAllIds();
        if (!empty($notAnchorCategoryIds)) {
            return ['anchor' => array_diff($ids, $notAnchorCategoryIds), 'not_anchor' => $notAnchorCategoryIds];
        } else {
            return ['anchor' => $ids, 'not_anchor' => []];
        }
    }

    /**
     * @param $popupId
     * @param $data
     */
    public function updateDataToDb($popupId, $data)
    {
        foreach ($data as $dt) {
            $this->saveLayout($dt, $popupId);
        }
    }

    /**
     * @param $data
     * @param $popupId
     */
    public function saveLayout($data, $popupId)
    {
        try {
            if (isset($data['layout_id']) && $data['layout_id'] == 0) {
                unset($data['layout_id']);
            }
            $data['popup_id'] = $popupId;
            $model = $this->layoutFactory->create()->setData($data)->save();
            $layoutId = $model->getId();
            $entities = $data['entities'];
            if ($entities == "" || $data['page_group'] == 'all_pages') {
                $handle = $data['layout_handle'];
                $PageFor = $data['page_for'];
                if ($PageFor != 'specific') {
                    $this->createNewLayoutUpadte(['layout_id' => $layoutId, 'popup_id' => $popupId, 'handle' => $handle]);
                }
            } else {
                $handle = $data['page_group'];
                $handleList = $this->handleList();
                $ids = explode(",", $entities);
                foreach ($ids as $id) {
                    $this->createNewLayoutUpadte(['layout_id' => $layoutId, 'popup_id' => $popupId, 'handle' => $handleList[$handle] . $id]);
                }
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * @param $pageGroups
     * @param $popupId
     * @return array
     */
    public function formatData($pageGroups, $popupId)
    {
        $issetPageGroups = [];
        $tmpPageGroups = [];
        if ($pageGroups) {
            foreach ($pageGroups as $pageGroup) {
                if (isset($pageGroup[$pageGroup['page_group']])) {
                    $pageGroupData = $pageGroup[$pageGroup['page_group']];
                    if ($pageGroupData['page_id'] != 0) {
                        $issetPageGroups[] = $pageGroupData['page_id'];
                    }
                    $tmpPageGroup = [
                        'layout_id' => $pageGroupData['page_id'],
                        'popup_id' => $popupId,
                        'page_group' => $pageGroup['page_group'],
                        'layout_handle' => $pageGroupData['layout_handle'],
                        'page_for' => $pageGroupData['for'],
                        'entities' => '',
                    ];
                    if ($pageGroupData['for'] == \Magento\Widget\Model\Widget\Instance::SPECIFIC_ENTITIES) {
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                    }
                    if ($pageGroup['page_group'] == 'all_pages' && isset($pageGroupData['entities']) && is_array($pageGroupData['entities'])) {
                        $pageGroupData['entities'] = implode(",",$pageGroupData['entities']);
                        $tmpPageGroup['entities'] = $pageGroupData['entities'];
                    }
                    $tmpPageGroups[] = $tmpPageGroup;
                }
            }
        }
        return ['data' => $tmpPageGroups, 'isset' => $issetPageGroups];
    }

    /**
     * @param $popupId
     * @param $isset
     */
    public function deleteOldLayout($popupId, $isset)
    {
        $this->layoutResourceModel->deleteOldLayout($popupId, $isset);
    }

    /**
     * @param $layoutId
     */
    public function deleteOldLayoutUpadte($popupId)
    {
        $this->layoutResourceModel->deleteOldLayoutUpadte($popupId);
    }

    /**
     * @param $data
     */
    protected function createNewLayoutUpadte($data)
    {
        $this->layoutResourceModel->createNewLayoutUpadte($data);
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function handleList()
    {
        $data = ['anchor_categories' => 'catalog_category_view_id_', 'notanchor_categories' => 'catalog_category_view_id_', 'all_products' => 'catalog_product_view_id_'];
        foreach ($this->productType->getTypes() as $typeId => $type) {
            $data[$typeId . '_products'] = 'catalog_product_view_id_';
        }
        return $data;
    }

    /**
     * @param $postData
     * @return mixed
     */
    public function filterPostData($postData)
    {
        $postData['storeview'] = !empty($postData['storeview']) ? implode(",", $postData['storeview']) : "";
        $postData['customer_group'] = !empty($postData['customer_group']) ?
            implode(",", $postData['customer_group']) : "";
        $postData['page_display'] = !empty($postData['page_display']) ? implode(",", $postData['page_display']) : "";
        return $postData;
    }

    /**
     * @param $fromDate
     * @param $toDate
     * @return bool
     */
    public function validateRangeDate($fromDate, $toDate)
    {
        $fromDateNumber = $this->convertToTime($fromDate);
        $toDateNumber = $this->convertToTime($toDate);
        if (((int)$toDateNumber - (int)$fromDateNumber >= 0) || !$toDate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $timeString
     * @return false|int
     */
    protected function convertToTime($timeString)
    {
        $time = strtotime($timeString);
        return $time;
    }
}
