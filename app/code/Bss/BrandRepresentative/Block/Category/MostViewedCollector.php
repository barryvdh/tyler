<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Block\Category;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
/**
 * Class MostViewedCollector
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

    public function getTemplate()
    {
        return "Bss_BrandRepresentative::brand/most_viewed_collector.phtml";
    }

    /**
     * @return bool
     */
    public function isBrandCategory()
    {
        return $this->getCurrentCategory()->getLevel() >= self::IS_BRAND_CATEGORY_LV;
    }

    /**
     * Get current category id
     *
     * @return int|null
     */
    public function getAddNewVisitUrl()
    {
        return $this->getUrl(
            'bss_brandRepresentative/traffic/newVisit',
            ['category_id' => $this->getCurrentCategory()->getId()]
        );
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
}
