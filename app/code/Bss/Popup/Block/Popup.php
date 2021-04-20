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

namespace Bss\Popup\Block;

use Magento\Framework\Exception\NoSuchEntityException;

class Popup extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Bss_Popup::popup.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Popup Helper
     *
     * @var \Bss\Popup\Helper\Data
     */
    protected $helper;

    /**
     * Filter Provider
     *
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var \Bss\Popup\Model\PopupFactory
     */
    protected $popupFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Popup constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Bss\Popup\Helper\Data $helper
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Bss\Popup\Helper\Data $helper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->filterProvider = $filterProvider;
        $this->helper = $helper;
        $this->popupFactory = $popupFactory;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getBlockType()
    {
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        $route = $this->request->getRouteName();
        $handle = $route . '_' . $controller . '_' . $action;
        if ($handle == 'catalog_category_view' && $this->coreRegistry->registry('current_category')) {
            $category = $this->coreRegistry->registry('current_category');
            if ($category->getIsAnchor() == 1) {
                $type = 'layered';
            } else {
                $type = 'default';
            }
            return ['catalog_category_view_type_' . $type, 'catalog_category_view_id_' . $category->getId()];
        }
        if ($handle == 'catalog_product_view' && $this->coreRegistry->registry('current_product')) {
            $product = $this->coreRegistry->registry('current_product');
            return ['catalog_product_view', 'catalog_product_view_type_' . $product->getTypeId(), 'catalog_product_view_id_' . $product->getId()];
        }
        return [$handle];
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        return $storeId;
    }

    /**
     * @param $stringContent
     * @return string
     * @throws \Exception
     */
    public function filterContent($stringContent)
    {
        return $this->filterProvider->getPageFilter()->filter($stringContent);
    }

    /**
     * @return array
     */
    public function getPagesViewed()
    {
        return $this->helper->getSessionPageViewedByCustomer();
    }

    /**
     * @param $popup
     * @return string
     */
    public function getAnimation($popup)
    {
        if ($popup['effect_display'] == \Bss\Popup\Model\Source\Animation::ZOOM) {
            return "mfp-zoom-in";
        }
        if ($popup['effect_display'] == \Bss\Popup\Model\Source\Animation::HORIZONTAL) {
            return "mfp-move-horizontal";
        }
        if ($popup['effect_display'] == \Bss\Popup\Model\Source\Animation::FROM_TOP) {
            return "mfp-move-from-top";
        }
        if ($popup['effect_display'] == \Bss\Popup\Model\Source\Animation::UNFOLD_3D) {
            return "mfp-3d-unfold";
        }
        if ($popup['effect_display'] == \Bss\Popup\Model\Source\Animation::ZOOM_OUT) {
            return "mfp-zoom-out";
        }
        return " ";
    }

    /**
     * @param $popup
     * @return bool|int
     */
    public function popupIsAllowedDisplay($popup)
    {
        if ($this->isPreviewMode()) {
            // If it is preview mode
            // Then allow display without check condition
            return 1;
        }
        return $this->helper->popupIsAllowedDisplay($popup);
    }

    /**
     * Get Data Popup
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPopup()
    {
        $isPreview = $this->getData('preview');
        if ($isPreview && !empty($isPreview)) {
            // If it is preview mode
            // Then get data from params instead of database load
            return $isPreview;
        }
        $popupId = $this->helper->getPopup($this->getBlockType(), $this->getStoreId());
        if ($popupId) {
            return $this->popupFactory->create()->load($popupId)->getData();
        }
        return false;
    }

    /**
     * Get type template
     *
     * @return bool|string
     * @throws NoSuchEntityException
     */
    public function getTypeTemplatePopup()
    {
        $dataPopup = $this->getPopup();
        if ($dataPopup) {
            return $dataPopup["type_template"];
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPreviewMode()
    {
        return $this->getData('mode') === 'display_all' ? 1 : 0;
    }
}
