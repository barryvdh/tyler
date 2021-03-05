<?php
declare(strict_types=1);
namespace Bss\HideProductField\Plugin\Product;

use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Controller\Adminhtml\Product\Save as BePlugged;
use Magento\Framework\AuthorizationInterface;

/**
 * Class SetDefaultQuantity
 * And escape for saving
 * @see BePlugged
 */
class SetDefaultQuantity
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * SetDefaultQuantity constructor.
     *
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization
    ) {
        $this->helper = $helper;
        $this->authorization = $authorization;
    }

    /**
     * Set default quantity for product if the quantity attribute be hide
     *
     * @param BePlugged $subject
     */
    public function beforeExecute(
        BePlugged $subject
    ) {
        $postData = ($subject->getRequest()->getPostValue());
        if (in_array(
            "quantity_and_stock_status",
            explode(",", $this->helper->getAdditionalAttributeConfig())
        ) && $this->helper->isEnable()) {
            if (!$postData['product']['quantity_and_stock_status']['qty']) {
                $postData['product']['quantity_and_stock_status']['qty'] = 1;
            }
            if (isset($postData['product']['stock_data']['qty']) &&
                !$postData['product']['stock_data']['qty']
            ) {
                $postData['product']['stock_data']['qty'] = 1;
            }
            if (isset($postData['type_id']) && $postData['type_id'] == "downloadable") {
                unset($postData['weight']);
            }
        }
        $this->authorizeSavingOf($postData['product']);
        $subject->getRequest()->setPostValue($postData);
    }

    /**
     * Remove the design fields if not allowed to edit to escape the error
     * 'Not allowed to edit the product\'s design attributes'
     *
     * @param array $postData
     */
    private function authorizeSavingOf(&$postData)
    {
        if (!$this->authorization->isAllowed('Magento_Catalog::edit_product_design')) {
            unset($postData['page_layout']);
            unset($postData['options_container']);
        }
    }
}
