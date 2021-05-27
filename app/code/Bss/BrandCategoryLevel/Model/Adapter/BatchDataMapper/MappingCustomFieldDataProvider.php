<?php
declare(strict_types=1);

namespace Bss\BrandCategoryLevel\Model\Adapter\BatchDataMapper;

use Bss\BrandRepresentative\Model\ResourceModel\MostViewed;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * Class MappingCustomFieldDataProvider
 * Mapping custom field to elasticsearch index
 */
class MappingCustomFieldDataProvider implements AdditionalFieldsProviderInterface
{
    /**
     * @var MostViewed
     */
    private $mostViewedResource;

    /**
     * MostViewedDataProvider constructor.
     *
     * @param MostViewed $mostViewedResource
     */
    public function __construct(
        MostViewed $mostViewedResource
    ) {
        $this->mostViewedResource = $mostViewedResource;
    }

    /**
     * Mapping traffic value and newest value to elasticsearch index to make custom sort working
     *
     * @param array $productIds
     * @param int $storeId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFields(array $productIds, $storeId)
    {
        $fields = [];

        $mostViewedData = $this->mostViewedResource->getMostViewedData();
        $createdTime = $this->mostViewedResource->getCreateTimeProduct();
        foreach ($productIds as $id) {
            $traffic = 0;
            $createTime = 0; // is unix time. 0 is 1970-01-01 00:00:00
            if (isset($mostViewedData[$id])) {
                $traffic = (int) $mostViewedData[$id];
            }
            if (isset($createdTime[$id])) {
                // Need convert to unix timestamp
                $createTime = strtotime($createdTime[$id]);
            }

            $fields[$id] = [
                'most_viewed' => $traffic,
                'newest' => $createTime
            ];
        }

        return $fields;
    }
}
