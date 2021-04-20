<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductGridInlineEditor
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductGridInlineEditor\Plugin\Config;

/**
 * Class DefinitionCustomize
 *
 * @package Bss\ProductGridInlineEditor\Plugin\Config
 */
class DefinitionCustomize
{
    /**
     * Const custom definition
     */
    const BSS_DEFINITION_KEY = 'bssDefinitionKey';

    /**
     * @param \Magento\Ui\Config\Reader\Definition\Data $definitionData
     * @param \Closure $proceed
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function aroundGet(
        \Magento\Ui\Config\Reader\Definition\Data $definitionData,
        \Closure $proceed,
        $key,
        $default = null
    ) {
        if ($key == self::BSS_DEFINITION_KEY) {
            $arr = [
                self::BSS_DEFINITION_KEY => [
                    "arguments" => [
                        "data" => [
                            "config" => [
                                "component" => "Bss_ProductGridInlineEditor/js/grid/columns/multiselect"
                            ]
                        ]
                    ],
                    "attributes" => [
                        "class" => \Magento\Ui\Component\MassAction\Columns\Column::class,
                        "component" => "Bss_ProductGridInlineEditor/js/grid/columns/multiselect"
                    ],
                    "children" => [],
                    "uiComponentType" => self::BSS_DEFINITION_KEY
                ]
            ];
            $definitionData->merge($arr);
        }
        return $proceed($key, $default);
    }
}
