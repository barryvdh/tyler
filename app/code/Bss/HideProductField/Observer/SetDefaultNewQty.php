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
 * Set default qty for product and no manage stock for downloadable product created by brand manager
 */
class SetDefaultNewQty implements \Magento\Framework\Event\ObserverInterface
{
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
     * @param AggregateCustomizeHelper $aggregateHelper
     * @param Data $moduleHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        AggregateCustomizeHelper $aggregateHelper,
        Data $moduleHelper,
        \Psr\Log\LoggerInterface $logger,
        ManagerInterface $messageManager
    ) {
        $this->aggregateHelper = $aggregateHelper;
        $this->moduleHelper = $moduleHelper;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    /**
     * Process set default qty = 1 and no manage stock for downloadable product if user is brand manager
     *
     * @param Observer $observer
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

                if ($product->getId() && $product->isObjectNew() && $isQtyBeHided) {
                    $product->setQuantityAndStockStatus(['qty' => '1', 'is_in_stock' => '1']);
                    if ($product->getTypeId() === "downloadable" || $product->getTypeId() === "virtual") {
                        $product->setStockData(['manage_stock' => '0', 'use_config_manage_stock' => '0']);
                    }
                    // Use model->save() to escape required attributes validation
                    $product->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
