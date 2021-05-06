<?php
/**
 * Class for Restrictcustomergroup CustomerLogout
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Observer;

class CustomerLogout implements \Magento\Framework\Event\ObserverInterface
{
    protected $_ruleFactory;
    protected $_restrictcustomergroupHelper;
    protected $_storeManager;
    protected $_currentStoreView;
    protected $_layoutFactory;
    protected $_coreRegistry;
    protected $filterProvider;
    protected $_productFactory;
    protected $date;
    protected $_coreSession;
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

    protected $_redirect;
    protected $_messageManager;
    protected $_categoryCollection;

    protected $compareProducts;
    protected  $_productloader;
    protected $_compareItemFactory;
    protected $_catalogProductCompareList;

    public function __construct(
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
        \FME\Restrictcustomergroup\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \Magento\Catalog\CustomerData\CompareProducts $compareProducts,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\ProductFactory $productloader
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_restrictcustomergroupHelper = $helper;
        $this->_storeManager = $storeManager;
        $this->_currentStoreView = $this->_storeManager->getStore();
        $this->_layoutFactory = $layoutFactory;
        $this->httpContext = $httpContext;
        $this->_urlBuilder = $urlBuilder;
        $this->_coreRegistry = $coreRegistry;
        $this->filterProvider = $filterProvider;
        $this->_productFactory = $productFactory;
        $this->_page = $page;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->date = $date;
        $this->_coreSession = $coreSession;
        $this->_categoryCollection = $categoryCollection;
        $this->compareProducts = $compareProducts;
        $this->_productloader = $productloader;
        $this->_compareItemFactory = $compareItemFactory;
        $this->_catalogProductCompareList = $catalogProductCompareList;
    }

    public function getFirstCategoryId()
    {
       $categories = $this->_categoryCollection->create()
           ->addAttributeToSelect('*')
           ->setStore($this->_storeManager->getStore()); //categories from current store will be fetched
       $categoryid = '';
       foreach ($categories as $category)
       {
          $categoryid = $category->getId();
          break;
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
    }
}
