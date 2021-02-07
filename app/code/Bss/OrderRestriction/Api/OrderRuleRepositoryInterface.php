<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * Class OrderRuleRepositoryInterface
 */
interface OrderRuleRepositoryInterface
{
    /**
     * Get order rule by product id
     *
     * @param int $productId
     * @return Data\OrderRuleInterface
     */
    public function getByProductId($productId);

    /**
     * Save the order rule
     *
     * @param Data\OrderRuleInterface|array $orderRule
     * @return bool
     * @throws CouldNotSaveException
     * @throws InputException
     */
    public function save($orderRule);
}
