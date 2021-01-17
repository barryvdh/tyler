<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model\ResourceModel;

/**
 * Class Customer get encrypted customer password
 */
class Customer
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * Get customer hash password
     *
     * @param int $customerId
     * @return false|string
     */
    public function getEncryptCustomerPassword(int $customerId)
    {
        try {
            $connection = $this->resource->getConnection();

            $select = $connection->select();
            $select->from(
                ['customer' => $this->resource->getTableName('customer_entity')],
                ['password_hash']
            );
            $select->where('entity_id = ?', $customerId);

            return $connection->fetchOne($select);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
