<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * @category   BSS
 * @package    Bss_HideProductField
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HideProductField\Ui\DataProvider\Product\Form\Modifier;

use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Downloadable\Model\Product\Type as DonwloadType;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

/**
 * Class HideField
 *
 * @package Bss\HideProductField\Ui\DataProvider\Product\Form\Modifier
 */
class HideField extends AbstractModifier
{
    /**
     * @var string[]
     */
    private $hideAttributes;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var \Bss\AggregateCustomize\Helper\Data
     */
    private $aggregateCustomizeHelper;

    /**
     * HideField constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param LocatorInterface $locator
     * @param Registry $registry
     * @param Data $helper
     * @param RequestInterface $request
     * @param \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        LocatorInterface $locator,
        Registry $registry,
        Data $helper,
        RequestInterface $request,
        \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper
    ) {
        $this->logger = $logger;
        $this->locator = $locator;
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->request = $request;
        $this->aggregateCustomizeHelper = $aggregateCustomizeHelper;
    }

    /**
     * Get config hide attributes
     *
     * @return string[]
     */
    private function getHideAttributes()
    {
        if (!$this->hideAttributes) {
            try {
                $this->hideAttributes = explode(',', $this->helper->getAdditionalAttributeConfig());
            } catch (\Exception $e) {
                $this->hideAttributes = [];
            }
        }

        return $this->hideAttributes;
    }

    /**
     * Modify Meta Data Adminhtml Product Form
     *
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isNoAction()) {
            return $meta;
        }
        $amastyRole = $this->coreRegistry->registry('current_amrolepermissions_rule');
        if ($amastyRole) {
            $amastyRoleData = $amastyRole->getData();
        }
        $hideAttributes = $this->getHideAttributes();
        $params = $this->request->getParams();
        if ($hideAttributes) {
            $product = $this->locator->getProduct();
            $productType = "";
            if ($product) {
                $productType = $product->getTypeId();
            }
            if (isset($params['type'])) {
                $productType = $params['type'];
            }

            if ($productType == DonwloadType::TYPE_DOWNLOADABLE || $productType == ProductType::TYPE_VIRTUAL) {
                $needProcessFields = [
                    'container_custom_block',
                    'container_custom_block_2',
                    'container_category_ids',
                    'attribute_set_id',
                    'container_price',
                    'container_weight',
                    'container_news_from_date'
                ];
                foreach ($meta as &$metaData) {
                    $countFlag = 0;
                    if (is_array($hideAttributes) && !empty($hideAttributes)) {
                        foreach ($hideAttributes as $attribute) {
                            if ($attribute == 'price' && isset($metaData['children']['container_' . $attribute])) {
                                $metaData['children']['container_' . $attribute]['arguments']['data']['config']['visible'] = 0;
                                continue;
                            }
                            if ($attribute == "quantity_and_stock_status" && isset($metaData['children']['quantity_and_stock_status_qty'])) {
                                unset($metaData['children']['container_quantity_and_stock_status']['children']['quantity_and_stock_status']['arguments']['data']['config']['imports']);
                                $metaData['children']['quantity_and_stock_status_qty']['arguments']['data']['config']['visible'] = 0;
                            }
                            if (isset($metaData['children']['container_' . $attribute]['children']
                                [$attribute]['arguments']['data']['config']['visible'])
                            ) {
                                $countFlag++;
                                $metaData['children']['container_' . $attribute]['children']
                                [$attribute]['arguments']['data']['config']['visible'] = 0;
                            }
                        }
                    }
                    // If all the child element is hide, the container should be hiee to
                    if ($countFlag == count($metaData['children'])) {
                        $metaData['arguments']['data']['config']['visible'] = 0;
                    }

                    foreach ($needProcessFields as $field) {
                        try {
                            if (isset($metaData['children'][$field])) {
                                switch ($field) {
                                    case "container_category_ids":
                                        /* Don't hide Categories if Amasty Role allow*/
                                        if (isset($amastyRoleData) &&
                                            !isset($amastyRoleData['categories']) &&
                                            !empty($amastyRoleData['categories'])
                                        ) {
                                            $metaData['children'][$field]['arguments']['data']['config']['visible'] = 0;
                                        }
                                        break;
                                    case "container_price":
                                        $metaData['children']['container_price']['children']['price']['arguments']['data']['config']['default'] = 0;
                                        break;
                                    default:
                                        $metaData['children'][$field]['arguments']['data']['config']['visible'] = 0;
                                        break;
                                }
                            }
                        } catch (\Exception $e) {
                            $this->logger->critical($e);
                        }
                    }
                }

                $sections = [
                    'websites', 'configurable', 'custom_options', 'related',
                    'search-engine-optimization', 'design', 'schedule-design-update', 'gift-options'
                ];
                foreach ($sections as $section) {
                    $meta = $this->processSection($meta, $section);
                }
            }
        }

        return $meta;
    }

    /**
     * Hide section.
     *
     * @param array $meta
     * @param string $section
     * @return array
     */
    private function processSection($meta, $section)
    {
        if (isset($meta[$section]) && $meta[$section]) {
            $meta[$section]['arguments']['data']['config']['visible'] = 0;
        }
        return $meta;
    }

    /**
     * No hide action
     *
     * @return bool
     */
    protected function isNoAction(): bool
    {
        $isEnable = $this->helper->isEnable();
        if (!$this->aggregateCustomizeHelper->isBrandManager() || !$isEnable) {
            return true;
        }

        return false;
    }

    /**
     * Modify Product Data Adminhtml Product Form
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        if ($this->isNoAction()) {
            return $data;
        }
        $productId = $this->locator->getProduct()->getId();
        $data[$productId]["visible_fields"] = [
            'gallery' => !in_array("gallery", $this->getHideAttributes())
        ];
        return $data;
    }
}
