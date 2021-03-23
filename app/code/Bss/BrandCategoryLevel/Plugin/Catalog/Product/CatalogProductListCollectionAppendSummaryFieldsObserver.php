<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Plugin\Catalog\Product;

use Bss\BrandCategoryLevel\Helper\Data;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Framework\Event\Observer as EventObserver;

class CatalogProductListCollectionAppendSummaryFieldsObserver
{
    /**
     * @var Toolbar
     */
    protected $toolbar;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var
     */
    protected $date;
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery
     */
    protected $sortQuery;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $layoutFactory;

    /**
     * ObserverSort constructor.
     * @param Data $helper
     * @param Toolbar $toolbar
     * @param \Magento\Framework\View\Page\Config $layoutFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery $sortQuery
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $helper,
        Toolbar $toolbar,
        \Magento\Framework\View\Page\Config $layoutFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery $sortQuery,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->sortQuery = $sortQuery;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->toolbar = $toolbar;
        $this->helper = $helper;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Description
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param $subject
     * @param EventObserver $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeExecute($subject, \Magento\Framework\Event\Observer $observer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
        $collection = $observer->getEvent()->getData('collection');
        $collection->load();
        $currentOrder = $this->toolbar->getCurrentOrder();
        switch ($currentOrder) {
            case "most_viewed":
                $this->sortQuery->sortByMostViewed($collection);
                $collection->clear();
                break;
            case "created_at":
                $this->sortQuery->sortByNewest($collection);
                $collection->clear();
                break;
            default:
                $collection->setOrder($currentOrder, $this->toolbar->getCurrentDirection());
        }

        $collection->loadData();

//        dd($collection->getItems());
//        $observer->setData('collection', $collection);
//        return [$observer];
    }

    /**
     * Description
     *
     * @param string $collection
     * @param int $storeId
     * @return int|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function productSortBy($collection, $storeId)
    {
        $webId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerSession->create();
        $cusId = ($customer->isLoggedIn()) ? ($customer->getCustomer()->getGroupId()) : 0;
        if ($this->checkValue('discount', $this->helper->getEnable('discount', $storeId))) {
            return $this->sortQuery->sortByDiscount($collection, $cusId, $webId);
        }
        if ($this->checkValue('dateSoldCount', $this->helper->getEnable('date_sold_count', $storeId))) {
            return $this->sortQuery->sortByDateSoldCount($collection, $storeId);
        }
        return 0;
    }

    /**
     * Description
     *
     * @param string $sort
     * @param int $enable
     * @return bool
     */
    protected function checkValue($sort, $enable)
    {
        if (($this->toolbar->getCurrentOrder() == $sort) && ($enable == 1)) {
            return true;
        }
        return false;
    }
}
