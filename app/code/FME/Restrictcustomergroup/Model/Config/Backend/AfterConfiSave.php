<?php
namespace FME\Restrictcustomergroup\Model\Config\Backend;

class AfterConfiSave extends \Magento\Framework\App\Config\Value
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

    public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\App\Config\ScopeConfigInterface $config,
    \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
    \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
    array $data = []
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
      parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
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

    public function beforeSave()
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
