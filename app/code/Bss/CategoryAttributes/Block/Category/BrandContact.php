<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Block\Category;

use Bss\CategoryAttributes\Helper\ConfigProvider;
use Magento\Framework\View\Element\Template;

/**
 * Class BrandContact
 * Category page view contact sidebar
 */
class BrandContact extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * BrandContact constructor.
     *
     * @param ConfigProvider $configProvider
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        ConfigProvider $configProvider,
        \Magento\Framework\Registry $coreRegistry,
        Template\Context $context,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get config popup options
     *
     * @return array
     */
    public function getPopupOptions()
    {
        return $this->configProvider->getPopupOptions();
    }

    /**
     * Get current category
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }
}
