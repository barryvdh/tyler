<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Catalog\Block\Product\ProductList\Toolbar;

class CatalogProductListCollectionCustomOrderFieldsObserver implements ObserverInterface
{
    /**
     * @var \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery
     */
    private $sortQuery;

    /**
     * @var Toolbar
     */
    private $toolbar;

    /**
     * CatalogProductListCollectionCustomOrderFieldsObserver constructor.
     *
     * @param \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery $sortQuery
     * @param Toolbar $toolbar
     */
    public function __construct(
        \Bss\BrandCategoryLevel\Model\ResourceModel\ObserverQuery\SortQuery $sortQuery,
        Toolbar $toolbar
    ) {
        $this->sortQuery = $sortQuery;
        $this->toolbar = $toolbar;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $productCollection = $observer->getEvent()->getCollection();

        if (!$productCollection->isLoaded()) {
            $currentOrder = $this->toolbar->getCurrentOrder();
            switch ($currentOrder) {
                case "most_viewed":
                    $this->sortQuery->sortByMostViewed($productCollection);
                    break;
                case "created_at":
                    $this->sortQuery->sortByNewest($productCollection);
                    break;
                default:
                    $productCollection->setOrder($currentOrder, $this->toolbar->getCurrentDirection());
            }

        }

        return $this;
    }
}
