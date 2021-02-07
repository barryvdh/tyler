<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin;

use Bss\OrderRestriction\Helper\AvailableToAddCart;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class AbstractGetProductDataInQuote
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
abstract class AbstractGetProductDataInQuote
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var AvailableToAddCart
     */
    protected $orderRuleValidation;

    /**
     * AbstractGetProductDataInQuote constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CustomerSession $customerSession
     * @param AvailableToAddCart $orderRuleValidation
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CustomerSession $customerSession,
        AvailableToAddCart $orderRuleValidation
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->orderRuleValidation = $orderRuleValidation;
    }

    /**
     * Get cart product data
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    protected function getProductData($quote)
    {
        $productData = [];
        foreach ($quote->getAllItems() as $item) {
            $parentItem = $item->getParentItem();
            $qty = $item->getQty();

            if ($parentItem && $parentItem->getProductType() == "configurable") {
                $qty = $parentItem->getQty();
            }

            if ($parentItem && $parentItem->getProductType() == "bundle") {
                $qty *= $parentItem->getQty();
            }

            if ($item->getProductType() == "configurable") {
                $qty = 0;
                if (isset($productData[$item->getProductIt()])) {
                    $qty = $productData[$item->getProductIt()];
                }
                $qty++;
            }

            $productDataItem = [
                "product_id" => $item->getProductId(),
                "qty" => $qty
            ];

            $productData[] = $productDataItem;
        }

        return $productData;
    }
}
