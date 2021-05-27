<?php
/**
 * Class for Restrictcustomergroup HideCatLayerNavigation
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin;

use Magento\Customer\Model\Session;
use \Magento\Store\Model\StoreManagerInterface;

class HideCatLayerNavigation
{
  /** @var _ruleFactory  */
  protected $_ruleFactory;

  /** @var _storeManager  */
  protected $_storeManager;

  /** @var date  */
  protected $date;

  /** @var _restrictcustomergroupHelper  */
  protected $_restrictcustomergroupHelper;

  /** @var httpContext  */
  protected $httpContext;

	public function __construct(
		\FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
    Session $customerSession,
    StoreManagerInterface $storeManager,
    \FME\Restrictcustomergroup\Helper\Data $helper,
    \Magento\Framework\Stdlib\DateTime\DateTime $date,
    \Magento\Framework\App\Http\Context $httpContext
	)
	{
		$this->_ruleFactory = $ruleFactory;
    $this->_customerSession = $customerSession;
    $this->_storeManager = $storeManager;
    $this->_restrictcustomergroupHelper = $helper;
    $this->date = $date;
    $this->httpContext = $httpContext;
	}

  public function afterBuild(\Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $subject, $result)
  {
    $currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
    $excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
    if (in_array($currentuserip, $excludediplist))
    {
      return $result;
    }
    if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
    {
			return $result;
		}

    $finalCategoriesList = [];
    $restrictcustomergroup = $this->_ruleFactory->create();
    $collection = $restrictcustomergroup->getCollection()
            ->addStoreFilter([$this->_storeManager->getStore()->getId()], false)
            ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
            ->addStatusFilter();

    $filteredcollection = $collection->getData();

    if (empty($filteredcollection))
  	{
  		return $result;
  	}

    $categoriesIdArray = [];
    $index = 0;
    foreach ($filteredcollection as $item)
		{
      if(empty($item['start_date']) || empty($item['end_date']))
			{
        if (!empty($item['categories_ids']))
        {
          $categoriesIdString = $item['categories_ids'];
          if (strpos($categoriesIdString, ',') !== false)
          {
            $categoriesIdArray =  explode(',',$categoriesIdString);
          }
          else
          {
            $categoriesIdArray[$index] = $categoriesIdString;
            $index = $index + 1;
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
          if (!empty($item['categories_ids']))
          {
            $categoriesIdString = $item['categories_ids'];
            if (strpos($categoriesIdString, ',') !== false)
            {
              $categoriesIdArray =  explode(',',$categoriesIdString);
            }
            else
            {
              $categoriesIdArray[$index] = $categoriesIdString;
              $index = $index + 1;
            }
          }
				}
      }
    }

    if (sizeof($categoriesIdArray) == 0)
    {
      return $result;
    }

    $indexforfinalcatarray = 0;
    foreach ($result as $key => $value)
    {
      if (in_array($value['value'], $categoriesIdArray))
      {
        // pass
        // means this is the category which needs to be excluded from layered navigation
      }
      else
      {
        $finalCategoriesList[$indexforfinalcatarray] =
          array('label' => $value['label'],'count' => $value['count'],'value' => $value['value']);
        $indexforfinalcatarray = $indexforfinalcatarray + 1;
      }
    }

    $result = $finalCategoriesList;
    return $result;
  }
}
