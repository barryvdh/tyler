<?php
/**
 * Class for Restrictcustomergroup Hide
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\HideMenu;

use Magento\Customer\Model\Session;

class Hide extends \Magento\Cms\Block\Widget\Block
{
  protected $_ruleFactory;
  protected $storeManager;
  protected $date;
  protected $_restrictcustomergroupHelper;
  protected $httpContext;
  protected $categoryRepository;

	public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Cms\Model\Template\FilterProvider $filterProvider,
    \Magento\Cms\Model\BlockFactory $blockFactory,
		\FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
    Session $customerSession,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \FME\Restrictcustomergroup\Helper\Data $helper,
    \Magento\Framework\Stdlib\DateTime\DateTime $date,
    \Magento\Framework\App\Http\Context $httpContext,
    \Magento\Catalog\Model\CategoryRepository $categoryRepository,
    array $data = []
	)
	{
		$this->_ruleFactory = $ruleFactory;
    $this->categoryRepository = $categoryRepository;
    $this->_customerSession = $customerSession;
    $this->storeManager = $storeManager;
    $this->_restrictcustomergroupHelper = $helper;
    $this->date = $date;
    $this->httpContext = $httpContext;
    parent::__construct($context, $filterProvider, $blockFactory, $data);
	}


  public function isModuleEnabled()
  {
    $flag = 0;
    if ($this->_restrictcustomergroupHelper->isEnabledInFrontend())
    {
			$flag = 1;
		}
    return $flag;
  }

  public function getRestrictedCategoriesIds()
  {
    $categoriesIdArray = [];
    $restrictcustomergroup = $this->_ruleFactory->create();
    $collection = $restrictcustomergroup->getCollection()
              ->addStoreFilter([$this->storeManager->getStore()->getId()], false)
              ->addCustomerGroupFilter($this->_restrictcustomergroupHelper->getCustomerGroupId())
              ->addStatusFilter();

    if (sizeof($collection) == 0)
    {
      return $categoriesIdArray;
    }

    $index = 0;
    foreach ($collection as $item)
    {
      if((empty($item->getData('start_date')) || empty($item->getData('end_date')))
        && !empty($item->getData('categories_ids')))
      {
        $categoriesIdString = $item->getData('categories_ids');
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
      else
      {
        $startDate = $item->getData('start_date');
        $endDate = $item->getData('end_date');
        $currentDate = $this->date->gmtDate();
        if ((($currentDate >= $startDate) && ($currentDate <= $endDate)) && !empty($item->getData('categories_ids')) )
        {
          $categoriesIdString = $item->getData('categories_ids');
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
    $categoriesIdArray = array_unique($categoriesIdArray);
    $categoriesUrlArray = [];
    $temp = [];
    foreach ($categoriesIdArray as $key => $catid)
    {
      $category = $this->categoryRepository->get($catid, $this->storeManager->getStore()->getId());
      $temp[] = $category->getUrl();
    }
    $categoriesUrlArray = $temp;
    return $categoriesUrlArray;
  }
}

























/*
hell;o
*/
