<?php
declare(strict_types=1);
namespace Bss\HideProductField\Plugin\Product;

use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Controller\Adminhtml\Product\Save as BePlugged;

/**
 * Class SetDefaultQuantity
 *
 * @see BePlugged
 */
class SetDefaultQuantity
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * SetDefaultQuantity constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Set default quantity for product if the quantity attribute be hide
     *
     * @param BePlugged $subject
     */
    public function beforeExecute(
        BePlugged $subject
    ) {
        if (in_array(
            "quantity_and_stock_status",
            explode(",", $this->helper->getAdditionalAttributeConfig())
        ) && $this->helper->isEnable()) {
            $postData = ($subject->getRequest()->getPostValue());
            if (!$postData['product']['quantity_and_stock_status']['qty']) {
                $postData['product']['quantity_and_stock_status']['qty'] = 1;
            }
            if (!$postData['product']['stock_data']['qty']) {
                $postData['product']['stock_data']['qty'] = 1;
            }
            $subject->getRequest()->setPostValue($postData);
        }
    }
}
