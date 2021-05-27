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

namespace Bss\BrandRepresentative\Observer\Sales\Order;

use Bss\BrandRepresentative\Helper\Data;
use Bss\BrandRepresentative\Model\ReportProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Report
 *
 * Bss\BrandRepresentative\Observer\Sales\Order
 */
class Report implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ReportProcessor
     */
    protected $reportProcessor;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * Report constructor.
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param ReportProcessor $reportProcessor
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        LoggerInterface $logger,
        Data $helper,
        ReportProcessor $reportProcessor,
        ManagerInterface $messageManager
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->reportProcessor = $reportProcessor;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getData('order');
        $this->reportProcessor->processSaveReport($order);
    }
}
