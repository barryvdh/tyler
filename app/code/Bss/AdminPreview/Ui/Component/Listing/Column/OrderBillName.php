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

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;

/**
 * Class Address
 */
class OrderBillName extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * OrderBillName constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param Data $dataHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->authorization = $authorization;
        $this->dataHelper = $dataHelper;
        $this->escaper = $escaper;
        $this->urlBuilder = $urlBuilder;

    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            if ($this->dataHelper->isEnable() && $this->authorization->isAllowed('Bss_AdminPreview::config_section')) {
                $storeId = $this->context->getFilterParam('store_id');
                foreach ($dataSource['data']['items'] as & $item) {
                    $item[$this->getData('name')] = $this->prepareItem($item, $storeId);
                }
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
    protected function prepareItem($item, $storeId)
    {
        $customerId = $item['customer_id'];
        if ($customerId) {
            $url = $this->urlBuilder->getUrl('customer/index/edit', ['id' => $customerId, 'store' => $storeId]);
            return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $url . '&quot;)">' . $this->escaper->escapeHtml(
                    str_replace("\n", '<br/>', $item[$this->getData('name')])
                ) . '</a>';
        } else {
            return $this->escaper->escapeHtml(
                str_replace("\n", '<br/>', $item[$this->getData('name')])
            );
        }
    }
}
