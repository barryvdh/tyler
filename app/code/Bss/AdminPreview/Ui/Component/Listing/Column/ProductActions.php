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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\AuthorizationInterface;
use Bss\AdminPreview\Helper\Data;
use Bss\AdminPreview\Plugin\FrontendUrl;

/**
 * Class ProductActions
 */
class ProductActions extends Column
{
    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * ProductActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FrontendUrl $frontendUrl
     * @param AuthorizationInterface $authorization
     * @param Data $dataHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $components
     * @param array $data
     */
    // @codingStandardsIgnoreStart
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FrontendUrl $frontendUrl,
        AuthorizationInterface $authorization,
        Data $dataHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->frontendUrl = $frontendUrl;
        $this->authorization = $authorization;
        $this->dataHelper = $dataHelper;
        if (!$this->dataHelper->isEnable() || $this->dataHelper->getProductGridPreviewColumn() == 'actions' ||
            !$this->authorization->isAllowed('Bss_AdminPreview::config_section')) {
            unset($data);
            $data = [];
        }
        $this->context = $context;
        $this->productFactory = $productFactory;
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
            $storeId = $this->context->getFilterParam('store_id');
            foreach ($dataSource['data']['items'] as &$item) {
                $product = $this->loadProduct($item['entity_id']);
                if ($storeId) $product->setStoreId($storeId);
                if ($product->getVisibility() != 1 && $product->getStatus() == 1) {
                    $item[$this->getData('name')]['preview'] = [
                        'href' => $this->getProductUrl($item['entity_id'], $storeId),
                        'label' => __('Preview')
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

}
