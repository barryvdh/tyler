<?php
declare(strict_types=1);

namespace Bss\CategoryAttributes\Block\Category;

use Bss\CategoryAttributes\Helper\ConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * BrandContact constructor.
     *
     * @param SerializerInterface $serializer
     * @param ConfigProvider $configProvider
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        SerializerInterface $serializer,
        ConfigProvider $configProvider,
        \Magento\Framework\Registry $coreRegistry,
        Template\Context $context,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->configProvider = $configProvider;
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Get js config data
     *
     * @param string $attributeCode
     * @return string
     */
    public function getJsConfigData(string $attributeCode): string
    {
        try {
            $category = $this->getCurrentCategory();

            if (!$category->getData($attributeCode)) {
                return "{}";
            }
            $popupOptions = $this->getPopupOptions();
            $popupOptions["href"] = $category->getData($attributeCode);

            return $this->serializer->serialize($popupOptions);
        } catch (\Exception $e) {
            return "{}";
        }
    }

    /**
     * Get config popup options
     *
     * @return array
     */
    public function getPopupOptions()
    {
        $popupOptions = $this->configProvider->getPopupOptions();
        $popupOptions["type"] = "iframe";

        return $popupOptions;
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
