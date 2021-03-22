<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Block\Product\ProductList;

/**
 * Class Toolbar
 */
class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    public function getAvailableOrders()
    {
        parent::getAvailableOrders();

        $this->removeOrderFromAvailableOrders('price');
        $this->addOrderToAvailableOrders(
            'most_viewed',
            __("Most Viewed")
        );
        $this->addOrderToAvailableOrders(
            'created_at',
            __("Newest")
        );
        return $this->_availableOrder;
    }

    public function setCollection($collection)
    {
        parent::setCollection($collection);
                if ($this->getCurrentOrder() === 'most_viewed') {
            // dump($this->getCurrentDirection());
            $orderExpr = new \Zend_Db_Expr('traffic IS NULL asc, traffic desc');
            $this->_collection->getSelect()->order([
                $orderExpr
            ]);
        }
        if ($this->getCurrentOrder() == "created_at") {
            $this->_collection->setOrder(
                $this->getCurrentOrder(),
                "desc"
            );
        }

//        dd($this->_collection->getSelect()->assemble());
//        foreach ($this->_collection->getItems() as $item) {
//            dump($item->getData('traffic'));
//        }
        return $this->_collection;
    }
}
