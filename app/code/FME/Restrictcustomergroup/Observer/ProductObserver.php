<?php
/**
 * Class for Restrictcustomergroup ProductObserver
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Observer;

class ProductObserver implements \Magento\Framework\Event\ObserverInterface
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

    protected $_redirect;
    protected $_messageManager;

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
        \Magento\Framework\Session\SessionManagerInterface $coreSession
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

        // ****************************************************
        // Product Pages
        // ****************************************************
        $ruleProducts = [];
        if ($product = $this->_coreRegistry->registry('current_product'))
        {
            foreach ($collection as $item)
            {
              if (strpos($item->getData('conditions_serialized'), '"conditions"') !== false)
        			{
                $rule = $this->_ruleFactory->create()
                        ->load($item->getId());

                if ($rule->getConditions()->validate($product))
                {
                    $ruleProducts[$item->getId()] = $product->getId();
                }
              }
            }

            $allRules = array_keys($ruleProducts);

            if (empty($allRules))
            {
              return;
            }
            else
            {
              $collection->addIdFilter($allRules)
                      ->addPriorityFilter()
                      ->addLimit();
            }

            $filteredcollection = $collection->getData();

            if (!empty($filteredcollection))
            {
              foreach ($filteredcollection as $item)
              {
                if(empty($item['start_date']) || empty($item['end_date']))
          			{
                  $currentItem = $item;
                  // error message
                  if ($currentItem['restricted_customer_response_type'] == 1)
                  {
                    try
                    {
                      throw new \Magento\Framework\Exception\LocalizedException(__($currentItem['error_msg']));
                    }
                    catch(\Exception $e)
                    {
                      $redirect_url = $this->_urlBuilder->getUrl('restrictcustomergroup/index/restricted');
                      $this->_redirect->setRedirect($redirect_url);
                      $this->_messageManager->addError( __($currentItem['error_msg']));
                    }
                  }
                  // redirect
                  else
                  {
                    $currentUrl = $this->_urlBuilder->getCurrentUrl();
                    $redirectTo = $currentItem['redirect_url'];
                    if (empty($redirectTo))
                    {
                      return;
                    }
                    if($currentUrl == $redirectTo)
                    {
                      return;
                    }
                    $observer->getControllerAction()
                            ->getResponse()
                            ->setRedirect($redirectTo);
                  }
                }
                else
                {
                  $startDate = $item['start_date'];
          				$endDate = $item['end_date'];
          				$currentDate = $this->date->gmtDate();
          				if (($currentDate >= $startDate) && ($currentDate <= $endDate))
          				{
                    $currentItem = $item;
                    // error message
                    if ($currentItem['restricted_customer_response_type'] == 1)
                    {
                      try
                      {
                        throw new \Magento\Framework\Exception\LocalizedException(__($currentItem['error_msg']));
                      }
                      catch(\Exception $e)
                      {
                        $redirect_url = $this->_urlBuilder->getUrl('restrictcustomergroup/index/restricted');
                        $this->_redirect->setRedirect($redirect_url);
                        $this->_messageManager->addError( __($currentItem['error_msg']));
                      }
                    }
                    // redirect
                    else
                    {
                      $currentUrl = $this->_urlBuilder->getCurrentUrl();
                      $redirectTo = $currentItem['redirect_url'];
                      if (empty($redirectTo))
                      {
                        return;
                      }
                      if($currentUrl == $redirectTo)
                      {
                        return;
                      }
                      $observer->getControllerAction()
                              ->getResponse()
                              ->setRedirect($redirectTo);
                    }
                  }
                  else
                  {
                    return;
                  }
                }
              }
            }
        }
        // ****************************************************
        // Product Pages End Here.
        // ****************************************************
    }
}
