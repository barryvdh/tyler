<?php
declare(strict_types=1);

namespace Bss\BrandLandingPage\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class Brand
 */
class Brand implements \Magento\Framework\Data\OptionSourceInterface
{
    const BRAND_CATEGORY_LEVEL = 3;

    /**
     * @var CategoryCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Brand constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param CategoryCollectionFactory $collectionFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CategoryCollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        $brands = $this->getBrandCategoryCollection();
        $options = [];
        foreach ($brands->getItems() as $item) {
            $options[] = [
                'value' => $item->getId(),
                'label' => $item->getName()
            ];
        }

        return $options;
    }

    /**
     * Get Brand collection
     *
     * @param bool $getActive
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|null
     */
    protected function getBrandCategoryCollection($getActive = true)
    {
        $isActive = 0;
        if ($getActive) {
            $isActive = 1;
        }
        try {
            return $this->collectionFactory->create()
                ->addFieldToSelect(['entity_id', 'name'])
                ->addAttributeToFilter('level', self::BRAND_CATEGORY_LEVEL)
                ->addAttributeToFilter('is_active', $isActive);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return null;
    }
}
