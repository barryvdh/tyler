<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Api;

use Bss\OrderRestriction\Exception\CouldNotLoadException;

/**
 * Order rule repository
 */
interface OrderRuleRepositoryInterface
{
    /**
     * Get by customer id
     *
     * @param int $customerId
     * @return Data\OrderRuleInterface
     * @throws CouldNotLoadException
     */
    public function get($customerId);

    /**
     * Save the object
     *
     * @param Data\OrderRuleInterface $orderRule
     * @return bool
     */
    public function save($orderRule);
}
