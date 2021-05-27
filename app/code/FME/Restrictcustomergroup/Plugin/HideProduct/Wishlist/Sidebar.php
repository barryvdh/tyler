<?php
/**
 * Class for Restrictcustomergroup HideProduct Wishlist Sidebar
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin\HideProduct\Wishlist;

class Sidebar
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

    public function afterGetSectionData(
        \Magento\Wishlist\CustomerData\Wishlist $subject,
         $result
    )
    {
      $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
      $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
      if (in_array($currentuserip, $excludediplist))
      {
        return $result;
      }
      $wishlistprodarray = $result;
      if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
      {
          return $wishlistprodarray;
      }
      $wishlistitemarray = $wishlistprodarray['items'];
      $restrictcustomergroup = $this->_ruleFactory->create();
      $collection = $restrictcustomergroup->getCollection()
              ->addStoreFilter([$this->storeManager->getStore()->getId()], false)
              ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
              ->addStatusFilter();
      if ($collection->count() < 1)
      {
        return $wishlistprodarray;
      }
      $tempwishlistitemarray = $wishlistitemarray;
      foreach ($wishlistitemarray as $key => $value)
      {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($value['product_id']);
        $ruleProducts = [];
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
              unset($tempwishlistitemarray[$key]);
            }
            else
            {
              $currentDate = $this->date->gmtDate();
              if (($currentDate >= $item['start_date']) && ($currentDate <= $item['end_date']))
              {
                unset($tempwishlistitemarray[$key]);
              }
            }
          }
        }
      }
      $wishlistitemarray = $tempwishlistitemarray;
      return [
          'counter' => count($wishlistitemarray),
          'items' => count($wishlistitemarray) ? $wishlistitemarray : [],
      ];
    }
}
