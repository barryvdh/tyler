<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Product\Sorting;

use Bss\BrandCategoryLevel\Helper\Data;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Block\Result;
use Magento\Framework\View\Element\Template;

class DefaultSort extends Template
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var Toolbar
     */
    protected $toolbar;
    /**
     * @var Result
     */
    protected $result;
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * DefaultSort constructor.
     * @param Template\Context $context
     * @param Toolbar $toolbar
     * @param LayerResolver $layerResolver
     * @param Data $helper
     * @param Result $result
     * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Toolbar $toolbar,
        LayerResolver $layerResolver,
        Data $helper,
        Result $result,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->catalogLayer = $layerResolver->get();
        $this->result = $result;
        $this->toolbar = $toolbar;
        $this->helper = $helper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * @param $subject
     * @param $result
     * @return \Bss\BrandCategoryLevel\Plugin\Catalog\Product\Sorting\DefaultSort
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetListOrders($subject, $result)
    {
        $category = $this->catalogLayer->getCurrentCategory();
        /* @var $category \Magento\Catalog\Model\Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders['relevance'] = __('Relevance');
        $result->getChildBlock('search_result_list')->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            $this->getSort()
        )->setDefaultSortBy(
            'relevance'
        );

        return $this;
    }

    /**
     * Description
     *
     * @return string
     */
    public function getSort()
    {
        $storeId = $this->helper->getCurrentStoreId();
        $sort = '';
        if ($this->helper->getEnable('general', $storeId) == 1) {
            if ($this->toolbar->getCurrentOrder()=='count') {
                $sort = strtolower($this->helper->getOrderBy('view_count', $storeId));
            }
            if ($this->toolbar->getCurrentOrder()=='rating') {
                $sort = strtolower($this->helper->getOrderBy('review_rating', $storeId));
            }
            if ($this->toolbar->getCurrentOrder()=="createdAt") {
                $sort = strtolower($this->helper->getOrderBy('created_at', $storeId));
            }
            if ($this->toolbar->getCurrentOrder()=="orderLast") {
                $sort = strtolower($this->helper->getOrderBy('order_last', $storeId));
            }
            if ($this->toolbar->getCurrentOrder()=="discount") {
                $sort = strtolower($this->helper->getOrderBy('discount', $storeId));
            }
            $sort = $this->checkValue($sort, $storeId);
            return $sort;
        }
        return 'asc';
    }

    /**
     * Description
     *
     * @param string $sort
     * @param int $storeId
     * @return string
     */
    public function checkValue($sort, $storeId)
    {
        $save = $sort;
        if ($this->toolbar->getCurrentOrder()=="dateSoldCount") {
            $save = strtolower($this->helper->getOrderBy('date_sold_count', $storeId));
        }
        if ($this->toolbar->getCurrentOrder()=="dateViewCount") {
            $save = strtolower($this->helper->getOrderBy('date_view_count', $storeId));
        }
        if ($this->toolbar->getCurrentOrder()=="averageView") {
            $save = strtolower($this->helper->getOrderBy('average_view_per_sale', $storeId));
        }
        return $save;
    }
}
