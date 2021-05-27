<?php
declare(strict_types=1);
namespace Bss\HideProductField\Plugin\Product;

use Bss\AggregateCustomize\Helper\Data as AggregateCustomizeHelper;
use Bss\HideProductField\Helper\Data;
use Magento\Catalog\Controller\Adminhtml\Product\Save as BePlugged;
use Magento\Framework\AuthorizationInterface;

/**
 * Class SetDefaultQuantity
 * And escape for saving
 * @see BePlugged
 */
class RemoveEditDesignAttributePermissions
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
     * @var AggregateCustomizeHelper
     */
    private $aggregateHelper;

    /**
     * SetDefaultQuantity constructor.
     *
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     * @param AggregateCustomizeHelper $aggregateHelper
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization,
        AggregateCustomizeHelper $aggregateHelper
    ) {
        $this->helper = $helper;
        $this->authorization = $authorization;
        $this->aggregateHelper = $aggregateHelper;
    }

    /**
     * Set default quantity for product if the quantity attribute be hide
     *
     * @param BePlugged $subject
     */
    public function beforeExecute(
        BePlugged $subject
    ) {
        $postData = $subject->getRequest()->getPostValue();
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
        if ($this->helper->isEnable() &&
            $this->aggregateHelper->isBrandManager() &&
            !$this->authorization->isAllowed('Magento_Catalog::edit_product_design')) {
            if (in_array(
                    "page_layout",
                    explode(",", $this->helper->getAdditionalAttributeConfig())
                ) ||
                in_array(
                    "options_container",
                    explode(",", $this->helper->getAdditionalAttributeConfig())
                )
            ) {
                unset($postData['page_layout']);
                unset($postData['options_container']);
            }
        }
    }
}
