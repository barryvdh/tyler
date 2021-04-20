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
 * @category  BSS
 * @package   Bss_AdminProductsGridwCategory
 * @author   Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license  http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminProductsGridwCategory\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\Input as ElementInput;

class Input extends AbstractFilter
{
    const NAME = 'filter_input';

    const COMPONENT = 'input';

    /**
     * Wrapped component
     *
     * @var ElementInput
     */
    protected $wrappedComponent;

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext()]
        );
        $this->wrappedComponent->prepare();
        // Merge JS configuration with wrapped component configuration
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);
        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );
        $this->applyFilter();
        parent::prepare();
    }

    /**
     * Apply filter
     *
     * @return void
     */
    protected function applyFilter()
    {
        $field = $this->getName();
        $this->filterData = $this->getContext()->getFiltersParams();
        if (isset($this->filterData[$field])) {
            $value = str_replace(['%', '_'], ['\%', '\_'], $this->filterData[$field]);
            if (!empty($value)) {
                if ($field == 'category' || $field == 'category_id') {
                    if (!$this->helper->getGeneralConfig('enable')) {
                        return;
                    }
                    $listId = $this->customFilter->catFilter($field, $value);
                    $filter = $this->filterBuilder->setConditionType('in')
                        ->setField('entity_id')
                        ->setValue(sprintf('%%%s%%', $listId))
                        ->create();
                } else {
                    $filter = $this->filterBuilder->setConditionType('like')
                        ->setField($field)
                        ->setValue(sprintf('%%%s%%', $value))
                        ->create();
                }
                $this->getContext()->getDataProvider()->addFilter($filter);
            }
        }
    }
}
