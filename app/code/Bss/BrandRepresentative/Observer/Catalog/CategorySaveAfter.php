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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandRepresentative\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CategorySaveAfter
 * Event that trigger after category save to save addition brand email data
 */
class CategorySaveAfter implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {

    }
}
