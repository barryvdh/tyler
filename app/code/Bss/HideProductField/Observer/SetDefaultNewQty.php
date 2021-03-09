<?php
declare(strict_types=1);
namespace Bss\HideProductField\Observer;

use Bss\AggregateCustomize\Helper\Data as AggregateCustomizeHelper;
use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class SetDefaultNewQty
 * Set default qty for product created by brand manager
 */
class SetDefaultNewQty implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var AggregateCustomizeHelper
     */
    private $aggregateHelper;

    /**
     * @var Data
     */
    private $moduleHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * SetDefaultNewQty constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param AggregateCustomizeHelper $aggregateHelper
     * @param Data $moduleHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        AggregateCustomizeHelper $aggregateHelper,
        Data $moduleHelper,
        \Psr\Log\LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->productRepository = $productRepository;
        $this->aggregateHelper = $aggregateHelper;
        $this->moduleHelper = $moduleHelper;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            if ($this->moduleHelper->isEnable() &&
                $this->aggregateHelper->isBrandManager()
            ) {
                /** @var Product $product */
                $product = $observer->getData('product');
                $isQtyBeHided = in_array(
                    "quantity_and_stock_status",
                    explode(",", $this->moduleHelper->getAdditionalAttributeConfig())
                );

                if ($product->isObjectNew() && $isQtyBeHided) {
                    $product->setStockData(['qty' => '1', 'is_in_stock' => '1']);
                    $product->setQuantityAndStockStatus(['qty' => '1', 'is_in_stock' => '1']);
                    // use model->save() to avoid required attributes errors
                    $product->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
