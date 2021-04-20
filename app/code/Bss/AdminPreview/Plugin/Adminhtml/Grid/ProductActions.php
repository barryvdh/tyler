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

namespace Bss\AdminPreview\Plugin\Adminhtml\Grid;

use Bss\AdminPreview\Plugin\FrontendUrl;

class ProductActions
{
    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface
     */
    protected $context;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * ProductActions constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param FrontendUrl $frontendUrl
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        FrontendUrl $frontendUrl
    )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->frontendUrl = $frontendUrl;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
    }

    /**
     * @param \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject
     * @param array $dataSource
     * @return array
     */
    public function afterPrepareDataSource(
        \Magento\Catalog\Ui\Component\Listing\Columns\ProductActions $subject,
        array $dataSource
    )
    {
        if (isset($dataSource['data']['items'])) {
            $storeId = $this->context->getFilterParam('store_id');
            if ($this->_dataHelper->isEnable() && $this->_dataHelper->getProductGridPreviewColumn() == 'actions'
                && $this->_authorization->isAllowed('Bss_AdminPreview::config_section')) {
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $item['entity_id'], 'store' => $storeId]
                        ),
                        'label' => __('Edit')
                    ];
                    $product = $this->loadProduct($item['entity_id']);
                    if ($product->getVisibility() != 1 && $product->getStatus() == 1) {
                        $item[$subject->getData('name')]['preview'] = [
                            'href' => $this->getProductUrl($item['entity_id'], $storeId),
                            'label' => __('Preview')
                        ];
                    }
                }
            } else {
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'catalog/product/edit',
                            ['id' => $item['entity_id'], 'store' => $storeId]
                        ),
                        'label' => __('Edit')
                    ];
                }

            }
        }

        return $dataSource;
    }

    /**
     * @param $id
     * @return \Magento\Catalog\Model\Product
     */
    private function loadProduct($id)
    {
        return $this->productFactory->create()->load($id);
    }

    /**
     * @param $product_id
     * @param $storeId
     * @return mixed
     */
    public function getProductUrl($product_id, $storeId)
    {
        return $this->frontendUrl->getFrontendUrl()
            ->getUrl('adminpreview/preview/index', ['product_id' => $product_id, 'store' => $storeId]);
    }

    /**
     * @param $product_id
     * @param $storeId
     * @return string
     */
    public function getProductUrlBackend($product_id, $storeId)
    {
        $url = $this->urlBuilder->getUrl('catalog/product/edit', ['id' => $product_id, 'store' => $storeId]);
        return $url;
    }

}