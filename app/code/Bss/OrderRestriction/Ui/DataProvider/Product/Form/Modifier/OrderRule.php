<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Ui\DataProvider\Product\Form\Modifier;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory;

/**
 * Data provider for order rule product restriction per month
 */
class OrderRule extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * OrderRule constructor.
     *
     * @param LocatorInterface $locator
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     */
    public function __construct(
        LocatorInterface $locator,
        OrderRuleRepositoryInterface $orderRuleRepository
    ) {
        $this->locator = $locator;
        $this->orderRuleRepository = $orderRuleRepository;
    }

    /**
     * Add order rule information
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        $stockData = &$data[$productId][self::DATA_SOURCE_DEFAULT][AdvancedInventory::STOCK_DATA_FIELDS];
        $orderRule = $this->orderRuleRepository->getByProductId($productId);

        if ($orderRule->getId()) {
            $stockData["sale_qty_per_month"] = $orderRule->getSaleQtyPerMonth();
            $stockData["sale_qty_per_month_origin"] = $orderRule->getSaleQtyPerMonth();
            $stockData["use_config_sale_qty_per_month"] = $orderRule->getUseConfig();
        }
        return $data;
    }

    /**
     * @inheritDoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
