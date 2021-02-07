<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

/**
 * Class AbstractDb
 * Interact with db
 */
abstract class AbstractDb
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * OrderedProduct constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->resource = $resource;
    }

    /**
     * Get table name
     *
     * @param string $name
     * @return string
     */
    public function getTable($name)
    {
        return $this->resource->getTableName($name);
    }
}
