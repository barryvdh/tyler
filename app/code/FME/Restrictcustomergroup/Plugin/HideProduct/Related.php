<?php
/**
 * Class for Restrictcustomergroup HideProduct Related
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin\HideProduct;

class Related
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

    public function afterGetItems(
        \Magento\Catalog\Block\Product\ProductList\Related $subject,
         $result
    )
    {
      $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
      $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
      if (in_array($currentuserip, $excludediplist))
      {
        return $result;
      }
      $relatedprodcollection = $result;
      if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
      {
          return $relatedprodcollection;
      }
      $restrictcustomergroup = $this->_ruleFactory->create();
      $collection = $restrictcustomergroup->getCollection()
              ->addStoreFilter([$this->storeManager->getStore()->getId()], false)
              ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
              ->addStatusFilter();
      if ($collection->count() < 1)
      {
        return $relatedprodcollection;
      }
      foreach ($relatedprodcollection as $key => $product)
      {
        $ruleProducts = [];
        foreach ($collection as $item)
        {
          if (strpos($item->getData('conditions_serialized'), '"conditions"') !== false)
          {
            $rule = $this->_ruleFactory->create()
                    ->load($item->getId());
            if ($rule->getConditions()->validate($product))
            {
              $ruleProducts[$item->getId()] = $product->getEntityId();
            }
          }
        }
        $allRules = array_keys($ruleProducts);
        if (empty($allRules))
        {
          continue;
        }
        $collection->addIdFilter($allRules)
                   ->addPriorityFilter()
                   ->addLimit();
        if (!empty($collection->getData()))
        {
          foreach ($collection->getData() as $item)
          {
            if(empty($item['start_date']) || empty($item['end_date']))
            {
              $relatedprodcollection->removeItemByKey($key);
            }
            else
            {
              $currentDate = $this->date->gmtDate();
              if (($currentDate >= $item['start_date']) && ($currentDate <= $item['end_date']))
              {
                $relatedprodcollection->removeItemByKey($key);
              }
            }
          }
        }
      }
      return $relatedprodcollection;
    }
}
