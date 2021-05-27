<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Block\Category;

use Bss\BrandRepresentative\Api\Data\MostViewedInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

/**
 * Class MostViewedCollector block
 */
class MostViewedCollector extends \Magento\Framework\View\Element\Template
{
    const IS_BRAND_CATEGORY_LV = 3;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * MostViewedCollector constructor.
     *
     * @param Registry $registry
     * @param Template\Context $context
     * @param array $data
     * phpstan:ignore
     */
    public function __construct(
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
    }

    /**
     * Set collector template
     *
     * @return string
     */
    public function getTemplate()
    {
        return "Bss_BrandRepresentative::brand/most_viewed_collector.phtml";
    }

    /**
     * Determine when to collect traffic data
     *
     * @return bool
     */
    public function isNeedCollectTraffic()
    {
        if ($this->isProductPage() ||
            ($this->getCurrentCategory() && $this->getCurrentCategory()->getLevel() >= self::IS_BRAND_CATEGORY_LV)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get current category id
     *
     * @return int|null
     */
    public function getAddNewVisitUrl()
    {
        if ($this->getCurrentCategory()) {
            $entityId = $this->getCurrentCategory()->getId();
        }
        $entityType = MostViewedInterface::TYPE_CATEGORY;

        if ($this->isProductPage()) {
            $entityId = $this->getCurrentProduct()->getId();
            $entityType = MostViewedInterface::TYPE_PRODUCT;
        }

        if (isset($entityId) && $entityId) {
            $this->getUrl(
                'bss_brandRepresentative/traffic/newVisit',
                ['entity_id' => $entityId, 'entity_type' => $entityType]
            );
        }
        return false;
    }

    /**
     * Is in product page
     *
     * @return bool
     */
    protected function isProductPage()
    {
        return $this->getRequest()->getFullActionName() == "catalog_product_view";
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }
}
