<?php
declare(strict_types=1);
namespace Bss\CustomizeAmastyRolePermissions\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Amasty\Rolepermissions\Helper\Data;
use Magento\Framework\Stdlib\ArrayManager;

/**
 * Class Categories
 */
class Categories
{
    /**
     * Container fieldset prefix
     */
    const CONTAINER_PREFIX = 'container_';

    /**
     * @var array
     */
    private $ruleCategories;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var Data
     */
    private $helper;

    /**
     * Categories constructor.
     *
     * @param ArrayManager $arrayManager
     * @param Data $helper
     */
    public function __construct(
        ArrayManager $arrayManager,
        Data $helper
    ) {
        $this->arrayManager = $arrayManager;
        $this->helper = $helper;
    }

    /**
     * Modify the category select field
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyMeta(
        \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Categories $subject,
        array $meta
    ) {
        $fieldCode = 'category_ids';
        $elementPath = $this->arrayManager->findPath($fieldCode, $meta, null, 'children');
        $containerPath = $this->arrayManager->findPath(self::CONTAINER_PREFIX . $fieldCode, $meta, null, 'children');

        if (!$elementPath || strpos($elementPath, $containerPath) === false) {
            return $meta;
        }

        $categoryIds = $this->arrayManager->get($elementPath, $meta);
        $categoryIdsOptionsPath = $this->arrayManager->findPath('options', $categoryIds);
        $categoryIdsOptions = $this->arrayManager->get($categoryIdsOptionsPath, $categoryIds);
        $rule = $this->helper->currentRule();

        if ($rule->getCategories()) {
            $this->ruleCategories = $rule->getCategories();
            $categoryIdsOptions['optgroup'] = $categoryIdsOptions;
            $this->recomposeCategoryTree($categoryIdsOptions);
            $categoryIdsOptions = $categoryIdsOptions['optgroup'];

            $categoryIds = $this->arrayManager->replace($categoryIdsOptionsPath, $categoryIds, $categoryIdsOptions);
            $meta = $this->arrayManager->replace($elementPath, $meta, $categoryIds);
        }

        return $meta;
    }

    /**
     * Recompose the category tree
     *
     * @param array $categoryOptions
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function recomposeCategoryTree(array &$categoryOptions): bool
    {
        if (isset($categoryOptions['optgroup'])) {
            $removeCategory = [];

            foreach ($categoryOptions['optgroup'] as $key => &$categoryOption) {
                if (!$this->recomposeCategoryTree($categoryOption)) {
                    $removeCategory[$key] = isset($categoryOption['optgroup']) ? $categoryOption['optgroup'] : false;
                }
            }

            if (!empty($removeCategory)) {
                foreach ($removeCategory as $key => $optgroups) {
                    unset($categoryOptions['optgroup'][$key]);

                    if ($optgroups) {
                        foreach ($optgroups as $optgroup) {
                            $categoryOptions['optgroup'][] = $optgroup;
                        }
                    }
                }

                $categoryOptions['optgroup'] = array_values($categoryOptions['optgroup']);
            }
        }

        return isset($categoryOptions['value']) && in_array($categoryOptions['value'], $this->ruleCategories);
    }
}
