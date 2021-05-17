<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Class GetProductEntityNextIncrementValue
 * Get next auto increment value in catalog_product_entity
 */
class GetProductEntityNextIncrementValue
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * GetProductEntityNextIncrementValue constructor.
     *
     * @param ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * Get auto increment value, catalog_product_entity table
     *
     * @return false|int
     */
    public function execute()
    {
        try {
            $connection = $this->resource->getConnection();
            $entityStatus = $connection->showTableStatus(
                $this->resource->getTableName("catalog_product_entity")
            );

            if (empty($entityStatus['Auto_increment'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
            }

            return $entityStatus['Auto_increment'];
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
