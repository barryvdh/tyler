<?php
namespace Bss\BrandCategoryLevel\Helper;

use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * get value config
 */
class Data extends AbstractHelper
{
    const SORTING_ORDER_CONFIG_PATH = 'productSorting/change/sorting_order';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $StoreManagerInterface;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $configCollectionFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\App\ResourceConnection $resource,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->fileSystem = $fileSystem;
        $this->StoreManagerInterface = $StoreManagerInterface;
        $this->resource = $resource;
        $this->logger = $logger;
        $this->configCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Get resource
     *
     * @return \Magento\Framework\App\ResourceConnection
     */
    public function getResourceHelp()
    {
        return $this->resource;
    }

    /**
     * Get logger
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Check enable
     *
     * @param string $sort
     * @param int $storeId
     * @return mixed
     */
    public function getEnable($sort, $storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/' . $sort . '/enable_module',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check enable
     *
     * @param string $sort
     * @param number $storeId
     * @return mixed
     */
    public function getEnableConfig($sort, $storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/' . $sort . '/enable_module',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Sort description
     *
     * @param string $sort
     * @param int $storeId
     * @return mixed
     */
    public function getOrderBy($sort, $storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/' . $sort . '/sort_up_down',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Sort description
     *
     * @param int $storeId
     * @return mixed
     */
    public function getTimeDateSoldCount($storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/date_sold_count/select_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Sort description
     *
     * @param int $storeId
     * @return mixed
     */
    public function getTimeDateViewCount($storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/date_view_count/select_time',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Sort description
     *
     * @param int $storeId
     * @return mixed
     */
    public function getDiscountSort($storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/discount/discount_sort',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Description
     *
     * @param int $storeId
     * @return mixed
     */
    public function getChange($storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/change/change_hidden',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }

    /**
     * Get Change Arrange Without Cache
     *
     * @param int $storeId
     * @param string $scope
     * @param string $path
     * @return array|bool|mixed|null
     */
    public function getConfigForceCache($storeId, $scope, $path)
    {
        $configCollection = $this->configCollectionFactory->create();
        $configCollection->addFieldToFilter('scope', $scope)
            ->addFieldToFilter('scope_id', $storeId)
            ->addFieldToFilter('path', ['eq' => $path]);
        if ($configCollection->getSize() > 0) {
            return $configCollection->setPageSize(1)->getLastItem()->getData('value');
        }
        return false;
    }

    /**
     * Get Change Default Value
     *
     * @return mixed
     */
    public function getSortingOrderDefault()
    {
        return $this->getConfigForceCache(
            0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            self::SORTING_ORDER_CONFIG_PATH
        );
    }

    /**
     * Get Sorting Front End
     *
     * @param int $storeId
     * @return array|bool|mixed|null
     */
    public function getSortingOrderStoreView($storeId)
    {
        return $this->getConfigForceCache(
            $storeId,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            self::SORTING_ORDER_CONFIG_PATH
        );
    }

    /**
     * Sort description
     *
     * @param int $storeId
     * @return mixed
     */
    public function robotTag($storeId)
    {
        return $this->scopeConfig->getValue(
            'productSorting/robot/robot_select',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * All sorting order
     *
     * @return array
     */
    public function makeArraySortingOrder()
    {
        return [
            'view_count' => [
                'label' => 'Most reviewed',
                'searchKey' => 'count'
            ],
            'created_at' => [
                'label' => 'Newest',
                'searchKey' => 'createdAt'
            ],
            'order_last' => [
                'label' => 'Recently ordered',
                'searchKey' => 'orderLast'
            ],
            'discount' => [
                'label' => 'On sale',
                'searchKey' => 'discount'
            ],
            'date_sold_count' => [
                'label' => 'Best selling',
                'searchKey' => 'dateSoldCount'
            ],
            'date_view_count' => [
                'label' => 'Most viewed',
                'searchKey' => 'dateViewCount'
            ],
            'average_view_per_sale' => [
                'label' => 'Average View Per Sale',
                'searchKey' => 'averageView'
            ],
            'review_rating' => [
                'label' => 'Review Rating Product',
                'searchKey' => 'rating'
            ]
        ];
    }

    /**
     * Get current Store Id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}
