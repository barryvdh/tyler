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

use Bss\BrandRepresentative\Model\ResourceModel\SalesReport\CollectionFactory;
use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * @var CollectionFactory
     */
    protected $reportCollectionFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepositoryInterface;

    /**
     * ReportSend constructor.
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param DateTime $date
     * @param RegionFactory $regionFactory
     * @param CategoryRepositoryInterface $CategoryRepositoryInterface
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Json $json,
        DateTime $date,
        RegionFactory $regionFactory,
        CategoryRepositoryInterface $CategoryRepositoryInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->reportCollectionFactory = $collectionFactory;
        $this->json = $json;
        $this->dateTime = $date;
        $this->regionFactory = $regionFactory;
        $this->categoryRepositoryInterface = $CategoryRepositoryInterface;
    }

    /**
     * Process send email by brand
     */
    public function processToSendEmail(): void
    {
        $collectionData = $this->getReportCollectionData();
        if (!empty($collectionData) && is_array($collectionData)) {
            $brandEmails = $this->gatherBrandEmails($collectionData);
            $this->prepareSendData($brandEmails, $collectionData);
        } else {
            $this->logger->critical(__('Empty data to send!'));
        }
    }

    /**
     * Convert RAW report collection to email report data
     *
     * @param array $brandEmails
     * @param array $collectionData
     * @return void
     */
    public function prepareSendData($brandEmails = [], $collectionData = []): void
    {
        if (is_array($brandEmails) && is_array($collectionData)) {
            foreach ($brandEmails as $email) {
                $email = preg_replace('/\s+/', '', $email);
                //Debug Section, comment if production mode -------
                //$this->logger->log(100, print_r($email, true));
                //$this->logger->log(100, print_r($this->prepareEmailData($email, $collectionData), true));
                //End debug section ------
                $this->sendMail($email, $this->prepareEmailData($email, $collectionData));
            }
        } else {
            $this->logger->critical(__('Email data is incorrect please check again!'));
        }
    }

    /**
     * Process Send email to brand representative.
     *
     * @param string $to
     * @param array $data
     */
    public function sendMail(string $to, array $data = []): void
    {
        try {
            $postObject = new DataObject();

            $postObject->setData($data);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('bss_daily_sale_report')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(
                    [
                        'name' => 'Admin',
                        'email' => 'one2onesales@gmail.com'
                    ]
                )
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {
            $this->logger->critical(__($e->getMessage()));
        }
    }

    /**
     * @return array
     */
    public function getReportCollectionData(): array
    {
        $collectionData = [];
        $collection = $this->reportCollectionFactory->create();
        //Filter previous day only;
        $reportDay = $this->dateTime->gmtDate('y-m-d', strtotime("yesterday"));
        $collection->addFieldToFilter('ordered_time', $reportDay);
        if ($collection->getSize() > 0) {
            $collectionData = $collection->getData();
        }
        return $collectionData;
    }

    /**
     * Gather email data unique
     *
     * @param $collectionData
     * @return array
     */
    public function gatherBrandEmails($collectionData): array
    {
        $brandEmails = [];
        foreach ($collectionData as $rowData) {
            if (isset($rowData['representative_email'])) {
                $categoryData = $this->json->unserialize($rowData['representative_email']);
                foreach ($categoryData as $emails) {
                    foreach ($emails as $email) {
                        $brandEmails[] = $email;
                    }
                }
            }
        }
        return array_unique($brandEmails);
    }

    /**
     * @param string $email
     * @param array $collectionData
     * @return array
     */
    public function prepareEmailData(string $email, array $collectionData): array
    {
        $data = [];
        if (is_array($collectionData)) {
            $data['report_time'] = (string)$this->dateTime->gmtDate('H:i:s y:m:d');
            foreach ($collectionData as $rowData) {
                if (isset($rowData['representative_email']) &&
                    $this->emailMatch($email, $rowData['representative_email'])
                ) {
//                    $brandName = '';
//                    try {
//                        $brand = $this->categoryRepositoryInterface->get($rowData['brand']);
//                        $brandName = $brand->getName();
//                    } catch (NoSuchEntityException $exception) {
//                        $this->logger->critical(__('Brand Not Found, ID: ') . $rowData['brand']);
//                    }

                    $data['report'][] = [
                        'order_id' => $rowData['order_id'],
                        'product_sku' => $rowData['product_sku'],
                        'product_name' => $rowData['product_name'],
                        'brand' => $rowData['brand'],
                        'product_type' => $rowData['product_type'],
                        'ordered_qty' => $rowData['ordered_qty'],
                        'ordered_time' => $rowData['ordered_time'],
                        'customer_name' => $rowData['customer_name'],
                        'address' => $rowData['address'],
                        'city' => $rowData['city'],
                        'province' => $rowData['province'],
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param string $email
     * @param string $representativeEmailList
     * @return bool
     */
    public function emailMatch(string $email, string $representativeEmailList): bool
    {
        if ($representativeEmailList) {
            $representativeEmailListArray = $this->json->unserialize($representativeEmailList);
            if ($representativeEmailListArray !== null && is_array($representativeEmailListArray)) {
                foreach ($representativeEmailListArray as $emailBrandPerCategory) {
                    foreach ($emailBrandPerCategory as $emailBrand) {
                        $emailBrand = preg_replace('/\s+/', '', $emailBrand);
                        if ((string)$emailBrand === $email) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
