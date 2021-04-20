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
namespace Bss\AdminPreview\Block\Adminhtml\Sales\Order\View\Items;

use Bss\AdminPreview\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Renderer
 *
 * @package Bss\AdminPreview\Block\Adminhtml\Sales\Order\View\Items
 */
class Renderer extends \Magento\Bundle\Block\Adminhtml\Sales\Order\View\Items\Renderer
{
    /**
     * Bss Helper
     *
     * @var Data
     */
    private $helperBss;

    /**
     * Renderer constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param Data $helperBss
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        Data $helperBss,
        array $data = [],
        Json $serializer = null
    ) {
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $messageHelper,
            $checkoutHelper,
            $data,
            $serializer
        );
        $this->helperBss = $helperBss;
    }

    /**
     * Get Bss Helper Data
     *
     * @return Data
     */
    public function getBssHelper()
    {
        return $this->helperBss;
    }
}
