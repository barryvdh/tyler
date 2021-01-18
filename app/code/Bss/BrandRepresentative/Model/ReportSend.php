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

namespace Bss\BrandRepresentative\Model;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Class ReportSend
 * Bss\BrandRepresentative\Model
 */
class ReportSend
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ReportSend constructor.
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * Convert RAW report collection to email report data
     *
     * @param array $collectionData
     * @return array
     */
    public function prepareSendData(array $collectionData = []): array
    {
        $data = [];
        if (!empty($collectionData)) {
            return $data;
        }
        return [];
    }

    /**
     * Process Send email to brand representative.
     *
     * @param string $to
     * @param array $data
     */
    public function sendMail(string $to, array $data = []): void
    {
        $report = [
            'report_date' => date("j F Y", strtotime('-1 day')),
            'orders_count' => rand(1, 10),
            'order_items_count' => rand(1, 10),
            'avg_items' => rand(1, 10)
        ];
        try {
            $postObject = new DataObject();
            $postObject->setData($report);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('daily_status_template')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(['name' => 'Robot','email' => 'robot@server.com'])
                ->addTo(['fred@server.com', 'otherguy@server.com'])
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {

        }
    }
}
