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
namespace Bss\ProductGridInlineEditor\Controller\Adminhtml\InlineEditor;

/**
 * Product grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends \Bss\ProductGridInlineEditor\Controller\Adminhtml\InlineEditor
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'status' => 'error',
            ]);
        }
        try {
            $skusHaschange = [];
            $storeId        = $this->getStoreId();
            $productIds     = array_keys($postItems);
            $productSkus   = $this->getSkus();
            $productsAttributesData = $this->getAttributesData();
            $productId      = current($productIds);
            $productSku    = isset($productSkus[$productId]) ? $productSkus[$productId] : null;
            $attributesData = isset($productsAttributesData[$productId]) ? $productsAttributesData[$productId] : [];

            $this->productAction->updateAttributes($productIds, $attributesData, $storeId);
            $this->updateInventory();
            $this->stockIndexerProcessor->reindexList($productIds);

            if ($productSku) {
                $product = $this->getLoadProduct($productId);
                $originalSku = $product->getSku();
                if ($productSku != $originalSku) {
                    $savedProduct = $this->saveSku($product, $productSku);
                    $skusHaschange[$product->getId()] = [
                        'name' => $savedProduct->getName(),
                        'sku' => $savedProduct->getSku()
                    ];
                }
            }

            $message = __('A total of %1 record(s) were updated.', count($productIds));
            $status = 'success';

            $this->productFlatIndexerProcessor->reindexList($productIds);

            if ($this->catalogProduct->isDataForPriceIndexerWasChanged($attributesData)) {
                $this->productPriceIndexerProcessor->reindexList($productIds);
            }

        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $message = $e->getMessage();
            $status = 'error';
        } catch (\Exception $e) {
            $message = __('Something went wrong while updating the product(s) attributes.');
            $status = 'error';
        }

        return $resultJson->setData([
            'message' => $message,
            'status' => $status,
            'skus_haschange' => $skusHaschange
        ]);
    }
}
