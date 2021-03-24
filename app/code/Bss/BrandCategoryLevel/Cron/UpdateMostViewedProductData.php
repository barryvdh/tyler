<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Cron;

use Magento\Framework\Indexer\IndexerInterfaceFactory as IndexerFactory;

/**
 * Class UpdateMostViewedProductData
 * Update most viewed product to elasticsearch job
 */
class UpdateMostViewedProductData
{
    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * UpdateMostViewedProductData constructor.
     *
     * @param IndexerFactory $catalogSearchFulltextIndexer
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        IndexerFactory $catalogSearchFulltextIndexer,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->indexerFactory = $catalogSearchFulltextIndexer;
        $this->logger = $logger;
    }

    /**
     * Update most viewed product data
     */
    public function execute()
    {
        try {
            $indexer = $this->indexerFactory->create();
            $indexer->load("catalogsearch_fulltext");
            $indexer->reindexAll();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
