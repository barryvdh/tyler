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
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Bss\BrandRepresentative\Helper\Data
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ReportSend constructor.
     *
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param DateTime $date
     * @param RegionFactory $regionFactory
     * @param CategoryRepositoryInterface $CategoryRepositoryInterface
     * @param TimezoneInterface $localeDate
     * @param \Bss\BrandRepresentative\Helper\Data $helper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Json $json,
        DateTime $date,
        RegionFactory $regionFactory,
        CategoryRepositoryInterface $CategoryRepositoryInterface,
        TimezoneInterface $localeDate,
        \Bss\BrandRepresentative\Helper\Data $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->reportCollectionFactory = $collectionFactory;
        $this->json = $json;
        $this->dateTime = $date;
        $this->regionFactory = $regionFactory;
        $this->categoryRepositoryInterface = $CategoryRepositoryInterface;
        $this->localeDate = $localeDate;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
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
            $defaultStoreId = $this->storeManager->getDefaultStoreView()->getId();
            $postObject->setData($data);

            $senderName = __("One to One Support")->getText();
            $senderEmail = $this->helper->getSalesEmailSender();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('bss_daily_sale_report')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $defaultStoreId])
                ->setTemplateVars(['data' => $postObject])
                ->setFrom(
                    [
                        'name' => $senderName,
                        'email' => $senderEmail
                    ]
                )
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Get report data
     *
     * @param string $stringTime
     * @return array
     */
    public function getReportCollectionData(string $stringTime = "yesterday"): array
    {
        $collectionData = [];
        $collection = $this->reportCollectionFactory->create();
        //Filter previous day only;
        $reportDay = $this->dateTime->gmtDate('y-m-d', strtotime($stringTime));
        $collection->addFieldToFilter('ordered_time', $reportDay);
        $collection->getSelect()->joinInner(
            [
                'sales' => $collection->getConnection()->getTableName("sales_order")
            ],
            'sales.entity_id = main_table.order_id',
            ["created_at"]
        );
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
            $data['report_time'] = (string)$this->localeDate->formatDate(null, \IntlDateFormatter::MEDIUM, true);
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

                    $orderTime = $this->localeDate->date(new \DateTime($rowData['created_at']));
                    $orderTime = $this->localeDate->formatDateTime(
                        $orderTime,
                        \IntlDateFormatter::MEDIUM,
                        \IntlDateFormatter::MEDIUM
                    );
                    $productOptions = [];
                    if (isset($rowData['product_options']) && $rowData['product_options']) {
                        try {
                            $productOptions = $this->json->unserialize($rowData['product_options']);
                        } catch (\Exception $e) {
                            $this->logger->critical(
                                __("Error when unserialize children product for email report: ") .
                                $e
                            );
                            $productOptions = [];
                        }
                    }
                    $productTypeOptions = $this->helper->getAllProductTypes();

                    if (isset($productTypeOptions[$rowData['product_type']])) {
                        $rowData['product_type'] = $productTypeOptions[$rowData['product_type']];
                    }

                    $data['report'][] = [
                        'order_id' => $rowData['order_id'],
                        'product_sku' => $rowData['product_sku'],
                        'product_name' => $rowData['product_name'],
                        'product_type' => $rowData['product_type'],
                        'product_options' => $productOptions ?? [],
                        'ordered_qty' => (int) $rowData['ordered_qty'],
                        'ordered_time' => $orderTime,
                        'company_name' => $rowData['company_name'],
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
