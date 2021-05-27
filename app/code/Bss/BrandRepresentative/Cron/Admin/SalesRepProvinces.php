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

namespace Bss\BrandRepresentative\Cron\Admin;

use Bss\BrandRepresentative\Model\ReportSend;
use Bss\BrandRepresentative\Model\ResourceModel\SalesReport\CollectionFactory;
use Exception;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;

/**
 * Class SalesRepProvinces
 * Cron that run every day at 9:00 AM
 * Send report of previous day to all Brand that having order.
 */
class SalesRepProvinces
{
    /**
     * @var ReportSend
     */
    protected $bssReportSend;

    /**
     * @var CollectionFactory
     */
    protected $bssReportCollectionFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SalesRepProvinces constructor.
     * @param ReportSend $reportSend
     * @param CollectionFactory $bssReportCollectionFactory
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param Emulation $emulation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ReportSend $reportSend,
        CollectionFactory $bssReportCollectionFactory,
        DateTime $dateTime,
        LoggerInterface $logger,
        Emulation $emulation,
        StoreManagerInterface $storeManager
    ) {
        $this->bssReportSend = $reportSend;
        $this->bssReportCollectionFactory = $bssReportCollectionFactory;
        $this->dateTime = $dateTime;
        $this->logger = $logger;
        $this->emulation = $emulation;
        $this->storeManager = $storeManager;
    }

    /**
     * Cron execute mail function
     */
    public function execute(): void
    {
        //Start frontend area emulation
        try {
            if ($this->storeManager->getDefaultStoreView()) {
//                $this->emulation->startEnvironmentEmulation(
//                    $this->storeManager->getDefaultStoreView()->getId(),
//                    Area::AREA_FRONTEND,
//                    true
//                );
                $this->bssReportSend->processToSendEmail();
//                $this->emulation->stopEnvironmentEmulation();
            } else {
                $this->logger->critical(__("Could not start emulation! No email was sent!"));
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
