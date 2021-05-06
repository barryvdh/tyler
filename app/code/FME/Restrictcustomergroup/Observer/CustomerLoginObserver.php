<?php
/**
 * Class for Restrictcustomergroup CustomerLoginObserver
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Observer;

use Magento\Customer\Model\Session;

class CustomerLoginObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $_ruleFactory;
    protected $_restrictcustomergroupHelper;
    protected $_storeManager;
    protected $_currentStoreView;
    protected $_layoutFactory;
    protected $_coreRegistry;
    public $filterProvider;
    protected $_productFactory;
    protected $_customerSession;

    /**
     * using current context to add values and
     * avoid request refresh for cookie values
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     *
     * @var \Magento\Framework\Url $urlBuilder
     */
    protected $_urlBuilder;

    /**
     * Page
     *
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;
    protected $date;
    protected $quoteRepository;

    protected $_redirect;
    protected $_categoryCollection;

    public function __construct(
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
        \FME\Restrictcustomergroup\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Cms\Model\Page $page,
        Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_restrictcustomergroupHelper = $helper;
        $this->_storeManager = $storeManager;
        $this->httpContext = $httpContext;
        $this->_currentStoreView = $this->_storeManager->getStore();
        $this->_urlBuilder = $urlBuilder;
        $this->_coreRegistry = $coreRegistry;
        $this->filterProvider = $filterProvider;
        $this->_productFactory = $productFactory;
        $this->_page = $page;
        $this->date = $date;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->_customerSession = $customerSession;
        $this->_customerCartSession = $cart;
        $this->_checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->_categoryCollection = $categoryCollection;
    }

    public function getFirstCategoryId()
    {
       $categories = $this->_categoryCollection->create()
           ->addAttributeToSelect('*')
           ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
       $categoryid = '';
       $counter = 1;
       foreach ($categories as $category)
       {
          if ($counter == 2)
          {
            $categoryid = $category->getId();
            break;
          }
          $counter = $counter + 1;
       }
       return $categoryid;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $categoryid = $this->getFirstCategoryId();
        if (!empty($categoryid))
        {
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $model = $objectManager->create('Magento\Catalog\Model\Category');
          $model->load($categoryid);
          $model->save();
        }

        $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
        $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
        if (in_array($currentuserip, $excludediplist))
        {
          return;
        }

        $url = $this->_urlBuilder->getCurrentUrl();
        $position = strpos($url, 'authorizenet');
        if ($position !== false)
        {
          return;
        }

        if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
        {
          return;
        }

        $request = $observer->getRequest();
        if ($this->_restrictcustomergroupHelper->isWebCrawler($request)) {
            return;
        }

        $restrictcustomergroup = $this->_ruleFactory->create();

        $collection = $restrictcustomergroup->getCollection()
                ->addStoreFilter([$this->_storeManager->getStore()->getId()], false)
                ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
                ->addStatusFilter();

        if ($collection->count() < 1)
        {
            return;
        }

        foreach ($collection as $item)
    		{
          if(empty($item->getData('start_date')) || empty($item->getData('end_date')))
    			{
            $ruleProducts = [];
            $total = 0;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $itemModel = $objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
            $q = $objectManager->create('\Magento\Quote\Model\Quote');

            $cart = $objectManager->create('Magento\Checkout\Model\Cart');
            $allItems = $cart->getQuote()->getItemsCollection();
            $customerQuote = $this->quoteFactory->create();

            foreach ($allItems as $item)
            {
                foreach ($collection as $items)
                {
                  $rule = $this->_ruleFactory->create()
                          ->load($items->getId());
                  if ($rule->getConditions()->validate($item->getProduct()))
                  {
                      $itemId = $item->getItemId();
                      $cart->removeItem($itemId)->save();
                      $cart->truncate();
                      $cart->save();
                  }

                }
            }
            $cart->getQuote()->collectTotals()->save();
            $cart->getQuote()->setTotalsCollectedFlag(false)->save();
            $itemModel->save($cart->getQuote());
          }
          else
          {
            $startDate = $item->getData('start_date');
    				$endDate = $item->getData('end_date');
    				$currentDate = $this->date->gmtDate();
    				if (($currentDate >= $startDate) && ($currentDate <= $endDate))
    				{
              $ruleProducts = [];
              $total = 0;

              $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
              $itemModel = $objectManager->create('\Magento\Quote\Api\CartRepositoryInterface');
              $q = $objectManager->create('\Magento\Quote\Model\Quote');

              $cart = $objectManager->create('Magento\Checkout\Model\Cart');
              $allItems = $cart->getQuote()->getItemsCollection();
              $customerQuote = $this->quoteFactory->create();

              foreach ($allItems as $item)
              {
                  foreach ($collection as $items)
                  {
                    $rule = $this->_ruleFactory->create()
                            ->load($items->getId());
                    if ($rule->getConditions()->validate($item->getProduct()))
                    {
                        $itemId = $item->getItemId();
                        $cart->removeItem($itemId)->save();
                        $cart->truncate();
                        $cart->save();
                    }

                  }
              }
              $cart->getQuote()->collectTotals()->save();
              $cart->getQuote()->setTotalsCollectedFlag(false)->save();
              $itemModel->save($cart->getQuote());
            }
            else
            {
                // rule is not valid b/w start and end date
                return;
            }
          }
        }
    }
}
