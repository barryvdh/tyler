<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Observer;

use Magento\Catalog\Model\Product\ProductList\Toolbar;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogProductListCollectionCustomOrderFieldsObserver
 * Set order and direction
 */
class CatalogProductListCollectionCustomOrderFieldsObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Toolbar
     */
    private $toolbar;

    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    private $listProductBlock;

    /**
     * CatalogProductListCollectionCustomOrderFieldsObserver constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Toolbar $toolbar
     * @param \Magento\Catalog\Block\Product\ListProduct $listProductBlock
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        Toolbar $toolbar,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock
    ) {
        $this->request = $request;
        $this->toolbar = $toolbar;
        $this->listProductBlock = $listProductBlock;
    }

    /**
     * Add order for catalog result search page, when doing this task,
     * the current order and direction not auto be setted to the collection
     * So i add them in here to make sure it works at the moment
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $observer->getData('collection');

        if ($this->request->getFullActionName() == "catalogsearch_result_index") {
            if (!$order = $this->toolbar->getOrder()) {
                $order = "relevance";
            }

            // if no direction in param, default is desc
            $dir = $this->toolbar->getDirection() ?: "desc";

            // force desc for custom sort
            if ($order == "most_viewed" ||
                $order == "newest"
            ) {
                $dir = "DESC";
            }

            $collection->setOrder(
                $order,
                $dir
            );
        }

        return $this;
    }
}
