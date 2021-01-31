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

use Magento\Framework\Registry;
use Bss\HideProductField\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Downloadable\Model\Product\Type as DonwloadType;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class HideField
 *
 * @package Bss\HideProductField\Ui\DataProvider\Product\Form\Modifier
 */
class HideField extends AbstractModifier
{
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
     * @param LocatorInterface $locator
     * @param Registry $registry
     * @param Data $helper
     * @param RequestInterface $request
     */
    public function __construct(
        LocatorInterface $locator,
        Registry $registry,
        Data $helper,
        RequestInterface $request
    ) {
        $this->locator = $locator;
        $this->coreRegistry = $registry;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Modify Meta Data Adminhtml Product Form
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $isEnable = $this->helper->isEnable();
        $amastyRole = $this->coreRegistry->registry('current_amrolepermissions_rule');
        $roleId = $amastyRole->getId();
        $hideAttributes = $this->helper->getAdditionalAttributeConfig();
        $params = $this->request->getParams();
        if ($roleId && $isEnable && $hideAttributes) {
            $product = $this->locator->getProduct();
            $productType = "";
            if ($product) {
                $productType = $product->getTypeId();
            }
            if (isset($params['type'])) {
                $productType = $params['type'];
            }
            $name = $this->getGeneralPanelName($meta);
            if ($productType == DonwloadType::TYPE_DOWNLOADABLE || $productType == ProductType::TYPE_VIRTUAL) {
                if ($name = $this->getGeneralPanelName($meta)) {
                    $hideAttributes = explode(',', $hideAttributes);
                    if (is_array($hideAttributes) && !empty($hideAttributes)) {
                        foreach ($hideAttributes as $attribute) {
                            if (isset($meta[$name]['children']['container_' . $attribute]['children']
                                [$attribute]['arguments']['data']['config']['visible'])
                            ) {
                                $meta[$name]['children']['container_' . $attribute]['children']
                                [$attribute]['arguments']['data']['config']['visible'] = 0;
                            }
                            
                        }
                    }
                    
                    try {
                        /* Custom container set */
                        $meta[$name]['children']['container_custom_block']['arguments']['data']['config']['visible'] = 0;
                        $meta[$name]['children']['container_custom_block_2']['arguments']['data']['config']['visible'] = 0;
                        $meta[$name]['children']['container_category_ids']['arguments']['data']['config']['visible'] = 0;

                        /* Attribute set */
                        $meta[$name]['children']['attribute_set_id']['arguments']['data']['config']['visible'] = 0;

                        /* Price */
                        $meta[$name]['children']['container_price']['arguments']['data']['config']['visible'] = 0;

                        /* Set default price attribute is 0 */
                        $meta[$name]['children']['container_price']['children']['price']['arguments']['data']['config']['default'] = 0;
                        
                        /* Stock and qty */
                        $meta[$name]['children']['quantity_and_stock_status_qty']['arguments']['data']['config']['visible'] = 0;
                        unset($meta[$name]['children']['container_quantity_and_stock_status']['children']['quantity_and_stock_status']['arguments']['data']['config']['imports']);

                        /* Weight */
                        $meta[$name]['children']['container_weight']['arguments']['data']['config']['visible'] = 0;

                        /* New from to */
                        $meta[$name]['children']['container_news_from_date']['arguments']['data']['config']['visible'] = 0;
                    } catch (\Exception $e) {

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
        if ($meta[$section] && isset($meta[$section])) {
            $meta[$section]['arguments']['data']['config']['visible'] = 0;
        }
        return $meta;
    }

    /**
     * Modify Product Data Adminhtml Product Form
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
