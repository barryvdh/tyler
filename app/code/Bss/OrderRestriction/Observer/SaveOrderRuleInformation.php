<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Observer;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveOrderRuleInformation
 * Save the order rule information: product limit per month after the product be save
 */
class SaveOrderRuleInformation implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * SaveOrderRuleInformation constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRuleRepositoryInterface $orderRuleRepository
    ) {
        $this->logger = $logger;
        $this->orderRuleRepository = $orderRuleRepository;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $observer->getData("product");

            $stockData = $product->getData("stock_data");

            if (isset($stockData["sale_qty_per_month"])) {
                if ($stockData["sale_qty_per_month"] == "") {
                    $stockData["sale_qty_per_month"] = null;
                }

                $orderRuleData = [
                    "product_id" => $product->getId(),
                    "sale_qty_per_month" => $stockData["sale_qty_per_month"],
                    "use_config_sale_qty_per_month" => $stockData["use_config_sale_qty_per_month"] ?? 1
                ];

                $this->orderRuleRepository->save($orderRuleData);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
