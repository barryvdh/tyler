<?php
/**
 * Class for Restrictcustomergroup RedirectFourOFour
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin;

class RedirectFourOFour
{
    /** @var _ruleFactory  */
    protected $_ruleFactory;

    /** @var _restrictcustomergroupHelper  */
    protected $_restrictcustomergroupHelper;

    /** @var date  */
    protected $date;

    /** @var httpContext  */
    protected $httpContext;

    /** @var _urlBuilder  */
    protected $_urlBuilder;

    /** @var _messageManager  */
    protected $_messageManager;

    /** @var _page  */
    protected $_page;

    /** @var resultRedirectFactory  */
    protected $resultRedirectFactory;

    protected $storeManager;

    public function __construct(
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
        \FME\Restrictcustomergroup\Helper\Data $helper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Cms\Model\Page $page,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_restrictcustomergroupHelper = $helper;
        $this->httpContext = $httpContext;
        $this->_page = $page;
        $this->_urlBuilder = $urlBuilder;
        $this->_messageManager = $messageManager;
        $this->date = $date;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->storeManager = $storeManager;
    }

    public function aroundExecute(
        \Magento\Cms\Controller\Noroute\Index $subject,
         callable $proceed
    )
    {
      $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
      $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
      if (in_array($currentuserip, $excludediplist))
      {
        return $proceed();
      }
      if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
      {
          return $proceed();
      }

      $restrictcustomergroup = $this->_ruleFactory->create();
      $collection = $restrictcustomergroup->getCollection()
              ->addStoreFilter([$this->storeManager->getStore()->getId()], false)
              ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
              ->addStatusFilter();

      $collectionflagmain = 1;
      if ($collection->count() < 1)
      {
        $collectionflagmain = 0;
      }

      if ($collectionflagmain == 1)
      {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $pageId = $objectManager->get(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_NO_ROUTE_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($pageId !== null)
        {
          $collectionPageFilter = $collection->addPageFilter($pageId)
                  ->addPriorityFilter()
                  ->addLimit();
          $collection->clear();
          $collectionflagsub = 1;
          if (count($collectionPageFilter->getAllIds()) < 1)
          {
            $collectionflagsub = 0;
          }
          if ($collectionflagsub == 1)
          {
            $filteredcollection = $collectionPageFilter->getData();
            if (!empty($filteredcollection))
            {
              foreach ($filteredcollection as $item)
              {
                if(empty($item['start_date']) || empty($item['end_date']))
                {
                  $currentItem = $item;
                  if ($currentItem['restricted_customer_response_type'] == 1)
                  {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $resultRedirect->setPath('restrictcustomergroup/index/restricted');
                    $this->_messageManager->addError( __($currentItem['error_msg']));
                    return $resultRedirect;
                  }
                  else
                  {
                    $currentUrl = $this->_urlBuilder->getCurrentUrl();
                    $redirectTo = $currentItem['redirect_url'];
                    if (!empty($redirectTo))
                    {
                      $resultRedirect = $this->resultRedirectFactory->create();
                      $resultRedirect->setPath($redirectTo);
                      return $resultRedirect;
                    }
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
                    if ($currentItem['restricted_customer_response_type'] == 1)
                    {
                      $resultRedirect = $this->resultRedirectFactory->create();
                      $resultRedirect->setPath('restrictcustomergroup/index/restricted');
                      $this->_messageManager->addError( __($currentItem['error_msg']));
                      return $resultRedirect;
                    }
                    else
                    {
                      $currentUrl = $this->_urlBuilder->getCurrentUrl();
                      $redirectTo = $currentItem['redirect_url'];
                      if (!empty($redirectTo))
                      {
                        $resultRedirect = $this->resultRedirectFactory->create();
                        $resultRedirect->setPath($redirectTo);
                        return $resultRedirect;
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      return $proceed();
    }
}
