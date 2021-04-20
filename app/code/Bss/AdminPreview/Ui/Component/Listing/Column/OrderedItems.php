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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;

/**
 * Class Address
 */
class OrderedItems extends Column
{

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * OrderedItems constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AuthorizationInterface $authorization
     * @param Data $dataHelper
     * @param LayoutFactory $layoutFactory
     * @param array $components
     * @param array $data
     */
    // @codingStandardsIgnoreStart
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        LayoutFactory $layoutFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->authorization = $authorization;
        $this->dataHelper = $dataHelper;
        if (!$this->dataHelper->isEnable() || !$this->authorization->isAllowed('Bss_AdminPreview::config_section')) {
            unset($data);
            $data = [];
        }
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);

    }
    // @codingStandardsIgnoreEnd

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        return $this->layoutFactory->create()->createBlock('Bss\AdminPreview\Block\Adminhtml\OrderedItems')
            ->assign('order', $item)->setTemplate('Bss_AdminPreview::ordereditems.phtml')->toHtml();
    }
}
