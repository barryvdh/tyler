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

namespace Bss\AdminPreview\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class CatalogProductViewObserver
 * @package Bss\AdminPreview\Observer
 */
class CatalogProductViewObserver implements ObserverInterface
{

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * CatalogProductViewObserver constructor.
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->dataHelper->isEnable()) {
            $product_preview = $observer->getEvent()->getProduct()->getBssAdminPreview();

            if ($product_preview == 1 && !$this->cookieManager->getCookie('adminLogged')) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(__('Product is not loaded'));
            }
        }
    }
}
