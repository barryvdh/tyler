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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute;


use Magento\Framework\DataObject;

/**
 * Class Validate
 *
 * @package Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute
 */
class Validate extends \Bss\CustomerAttributes\Controller\Adminhtml\Attribute\Validate
{
    /**
     * Validate constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Customer\Model\Attribute $attribute
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param DataObject $response
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Customer\Model\Attribute $attribute,
        \Magento\Catalog\Model\Product\Url $productUrl,
        DataObject $response
    ) {
        parent::__construct($context, $resultJsonFactory, $layoutFactory, $attribute, $productUrl, $response);
    }

    /**
     * Validate execute
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return parent::execute();
    }
}
