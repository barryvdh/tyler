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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\BrandRepresentative\Plugin\Model\Category;

use Bss\BrandRepresentative\Model\Config\Source\CountryOptions;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Element\Textarea;
use Magento\Ui\Component\Form\Field;

/**
 * Class DataProvider
 *
 * Bss\BrandRepresentative\Plugin\Model\Category
 */
class DataProvider
{
    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var ModuleManager
     * @since 101.0.0
     */
    protected $moduleManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CountryOptions
     */
    protected $countryOptions;

    /**
     * DataProvider constructor.
     * @param ModuleManager $moduleManager
     * @param RequestInterface $request
     * @param CountryOptions $countryOptions
     */
    public function __construct(
        ModuleManager $moduleManager,
        RequestInterface $request,
        CountryOptions $countryOptions
    ) {
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->countryOptions = $countryOptions;
    }

    /**
     * Add Meta to Category Form
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        array $meta
    ) {

        $data = $subject->getData();
        //$meta = $this->addBssDataProvider($meta, $data);
        return $meta;
    }

    /**
     * Bss Customer Group MetaData
     *
     * @param array $meta
     * @param array $data
     * @return array
     */
    private function addBssDataProvider(array $meta, $data): array
    {
        $meta['bss_brand_representative'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Brand Representative'),
                        'collapsible' => true,
                        'sortOrder' => 6,
                        'componentType' => 'fieldset',
                    ]
                ]
            ],
            'children' => [
                'bss_brand_representative_email' => $this->prepareDynamicRow(),
            ]
        ];
        return $meta;
    }

    /**
     * @return array
     */
    private function prepareDynamicRow(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'dynamicRows',
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'label' => __('Email Configuration'),
                        'renderDefaultRecord' => false,
                        'recordTemplate' => 'record',
                        'dataScope' => '',
                        'dndConfig' => [
                            'enabled' => false,
                        ],
                        'required' => false,
                        'sortOrder' =>'1',
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'dataScope' => '',
                            ],
                        ],
                    ],
                    'children' => [
                        'brand_emails' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'dataType' => Text::NAME,
                                        'formElement' => Textarea::NAME,
                                        'componentType' => Field::NAME,
                                        'dataScope' => 'brand_emails',
                                        'label' => __('Brand Emails'),
                                        'visible' => true,
                                        'sortOrder' => 10,
                                    ],
                                ],
                            ],
                        ],
                        'bss_country' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => Select::NAME,
                                        'componentType' => Field::NAME,
                                        'component' => 'Bss_BrandRepresentative/js/components/country',
                                        'dataType' => Text::NAME,
                                        'dataScope' => 'bss_country',
                                        'label' => __('Country'),
                                        'options' => $this->countryOptions->getAllOptions(),
                                        'value' => [],
                                        'sortOrder' => 20
                                    ],
                                ],
                            ],
                        ],
                        'bss_province' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'formElement' => MultiSelect::NAME,
                                        'componentType' => Field::NAME,
                                        'component' => 'Bss_BrandRepresentative/js/components/province',
                                        'dataType' => Text::NAME,
                                        'label' => __('Province'),
                                        'dataScope' => 'bss_province',
                                        'options' => [],
                                        'value' => [],
                                        'sortOrder' => 30,
                                        'imports' => [
                                            'countryId' => '${ $.provider }:${ $.parentScope }.bss_country'
                                        ],
                                        'listens' => [
                                            'countryId' => 'setCountryId'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'actionDelete' => [
                            'arguments' => [
                                'data' => [
                                    'config' => [
                                        'componentType' => 'actionDelete',
                                        'dataType' => Text::NAME,
                                        'label' => 'Action',
                                        'sortOrder' => 60,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
