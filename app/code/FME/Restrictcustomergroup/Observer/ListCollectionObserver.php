<?php
/**
 * Class for Restrictcustomergroup ListCollectionObserver
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Observer;

use Magento\Customer\Model\Session;

class ListCollectionObserver implements \Magento\Framework\Event\ObserverInterface
{
	protected $_ruleFactory;
	protected $_restrictcustomergroupHelper;
	protected $_storeManager;
	protected $_coreRegistry;
	protected $_productFactory;
	protected $_logger;
	protected $date;

	/**
	 *
	 * @var \Magento\Framework\Url $urlBuilder
	 */
	protected $_urlBuilder;
	protected $_customerSession;
  protected $_redirect;

	/**
	 * using current context to add values and
	 * avoid request refresh for cookie values
	 * @var \Magento\Framework\App\Http\Context
	 */
	protected $httpContext;

	public function __construct(
		\FME\Restrictcustomergroup\Model\RuleFactory $restrictcustomergroupFactory,
		\FME\Restrictcustomergroup\Helper\Data $helper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Framework\Url $urlBuilder,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		Session $customerSession,
		\Magento\Framework\App\Response\Http $redirect,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\App\Http\Context $httpContext
	) {
		$this->_ruleFactory = $restrictcustomergroupFactory;
		$this->_restrictcustomergroupHelper = $helper;
		$this->_storeManager = $storeManager;
		$this->_currentStoreView = $this->_storeManager->getStore();
		$this->_coreRegistry = $coreRegistry;
		$this->_productFactory = $productFactory;
		$this->_urlBuilder = $urlBuilder;
		$this->_logger = $logger;
		$this->_redirect = $redirect;
		$this->_messageManager = $messageManager;
		$this->date = $date;
		$this->_customerSession = $customerSession;
		$this->httpContext = $httpContext;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$currentuserip = $this->_restrictcustomergroupHelper->getRemoteIPAddress();
		$excludediplist = $this->_restrictcustomergroupHelper->getExcludedIP();
		if (in_array($currentuserip, $excludediplist))
		{
			return;
		}
		if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend()) {
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

		$ruleProducts = [];

		$excludeProducts = [];

		$productCollection = $observer->getCollection()->setPageSize(10)
			->setCurPage(1)->load();

		$productIds = $productCollection->getAllIds();
		foreach ($collection as $item)
		{
			if (strpos($item->getData('conditions_serialized'), '"conditions"') !== false)
			{
				if(empty($item->getData('start_date')) || empty($item->getData('end_date')))
				{
					foreach ($productIds as $productId)
					{
						if ($item->getConditions()->validateByEntityId($productId))
						{
							$ruleProducts[$item->getId()][] = $productId;
						}
					}
				}
				else
				{
					$startDate = $item->getData('start_date');
					$endDate = $item->getData('end_date');
					$currentDate = $this->date->gmtDate();
					if (($currentDate >= $startDate) && ($currentDate <= $endDate))
					{
						// valid rule because current date is in range
						foreach ($productIds as $productId)
						{
							if ($item->getConditions()->validateByEntityId($productId))
							{
								$ruleProducts[$item->getId()][] = $productId;
							}
						}
					}
					else
					{
						// not a valid rule because current date is out of range
					}
				}
			}
			else
			{
				// pass
			}
		}

		foreach ($ruleProducts as $ruleId => $matchedProducts)
		{
			foreach ($matchedProducts as $pid)
			{
				// return rule ids for matching product id
				$id = array_keys($ruleProducts);

				$collection->addIdFilter($id)
					->addPriorityFilter()
					->addLimit();

				$rule = $collection->getFirstItem();

				if ($rule->getId()) {
					$excludeProducts[] = $pid;
				}
			}
		}

		if (empty($excludeProducts))
		{
			return;
		}

		$finalExcludeProducts = array_values(array_unique($excludeProducts));

		$productCollection->clear()
			->addAttributeToSelect('*')
			->addAttributeToFilter('entity_id', ['nin' => $finalExcludeProducts]);
	}
}
