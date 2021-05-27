<?php
/**
 * Class for Restrictcustomergroup PostDispatch
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Observer;

class PostDispatch implements \Magento\Framework\Event\ObserverInterface
{
    protected $_ruleFactory;
    protected $_restrictcustomergroupHelper;
    protected $_storeManager;
    protected $_currentStoreView;
    protected $_layoutFactory;
    protected $_coreRegistry;
    public $filterProvider;
    protected $_productFactory;
    protected $date;
    protected $_coreSession;
    protected $httpContext;
    protected $serializer;
    protected $_urlBuilder;
    protected $_messageManager;
    protected $_redirect;
    protected $_page;
    private $responseFactory;

    public function __construct(
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
        \FME\Restrictcustomergroup\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Result\LayoutFactory $layoutFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_restrictcustomergroupHelper = $helper;
        $this->_storeManager = $storeManager;
        $this->_currentStoreView = $this->_storeManager->getStore();
        $this->_layoutFactory = $layoutFactory;
        $this->httpContext = $httpContext;
        $this->_coreRegistry = $coreRegistry;
        $this->filterProvider = $filterProvider;
        $this->_productFactory = $productFactory;
        $this->_page = $page;
        $this->_urlBuilder = $urlBuilder;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->date = $date;
        $this->_coreSession = $coreSession;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
        $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
        if (in_array($currentuserip, $excludediplist))
        {
          return;
        }

        $request = $observer->getRequest();
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

        if ($this->_restrictcustomergroupHelper->isWebCrawler($request))
        {
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

        // ****************************************************
        // Redirection
        // ****************************************************
        $currentUrl = $this->_urlBuilder->getCurrentUrl();

        $ruleIds = [];
        $redirectTo = [];

        foreach ($collection as $item)
        {
          $urls = $this->serializer->unserialize($item['url_serialized']);
          foreach ($urls as $key => $val)
          {
            if (in_array($currentUrl, $val))
            {
              $ruleIds[] = $item->getId(); //get rule id when matched
              $redirectTo[$item->getId()] = $urls[$key]['to']; // get correspondent url for the match
            }
          }
        }

        if (empty($redirectTo))
        {
          return;
        }

        $collection->addIdFilter($ruleIds)
                   ->addPriorityFilter()
                   ->addLimit();

        $firstItem = $collection->getData();

        if(empty($firstItem[0]['start_date']) || empty($firstItem[0]['end_date']))
        {
          if (empty($redirectTo[$firstItem[0]['rule_id']]) || $redirectTo[$firstItem[0]['rule_id']] == '' || ($currentUrl == $redirectTo[$firstItem[0]['rule_id']]))
          {
            $this->responseFactory->create()->setRedirect($currentUrl)->sendResponse();
            return $this;
          }
          else
          {
            $observer->getControllerAction()
                     ->getResponse()
                     ->setRedirect($redirectTo[$firstItem[0]['rule_id']]);
          }
        }
        else
        {
          $startDate = $firstItem[0]['start_date'];
          $endDate = $firstItem[0]['end_date'];
          $currentDate = $this->date->gmtDate();
          if (($currentDate >= $startDate) && ($currentDate <= $endDate))
          {
            if (empty($redirectTo[$firstItem[0]['rule_id']]) || $redirectTo[$firstItem[0]['rule_id']] == '' || ($currentUrl == $redirectTo[$firstItem[0]['rule_id']]))
            {
              $this->responseFactory->create()->setRedirect($currentUrl)->sendResponse();
              return $this;
            }
            else
            {
              $observer->getControllerAction()
                      ->getResponse()
                      ->setRedirect($redirectTo[$firstItem[0]['rule_id']]);
            }
          }
          else
          {
            return;
          }
        }
        // ****************************************************
        // Redirection End Here.
        // ****************************************************
    }
}
