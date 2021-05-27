<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Model\ResourceModel;

use Bss\BrandRepresentative\Model\MostViewed as Model;
use Bss\BrandRepresentative\Api\Data\MostViewedInterface;

/**
 * Class MostViewed Brands/Products
 */
class MostViewed extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE = 'bss_most_viewed';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(self::TABLE, Model::ID);
    }

    /**
     * Get the most viewed data of all entities in the database. Mapping by entity_id => traffic
     *
     * @param int $entityType
     * @return array
     */
    public function getMostViewedData($entityType = MostViewedInterface::TYPE_PRODUCT)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['main_table' => $this->getMainTable()], ['entity_id', 'traffic'])
                ->where('entity_type = ?', $entityType);

            return $connection->fetchPairs($select);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return [];
        }
    }

    /**
     * Get product create time
     *
     * @return array
     */
    public function getCreateTimeProduct()
    {
        try {
            $select = $this->getConnection()->select()
                ->from(
                    ['main_table' => $this->getTable('catalog_product_entity')],
                    ['entity_id', 'created_at']
                );

            return $this->getConnection()->fetchPairs($select);
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            return [];
        }
    }
}
