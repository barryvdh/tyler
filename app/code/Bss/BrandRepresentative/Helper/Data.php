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

namespace Bss\BrandRepresentative\Helper;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 * Bss\BrandRepresentative\Helper
 */
class Data extends AbstractHelper
{
    const FEATURED_BRANDS_CONFIG_PATH = "brand_representative/featured_brands/brand_ids";
    const SALES_EMAIL_SENDER_CONFIG_PATH = "trans_email/ident_sales/email";
    const SALES_SENDER_NAME_CONFIG_PATH = "trans_email/ident_sales/name";

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var BrandRepresentativeEmailDataRecursiveResolver
     */
    private $brandRepresentativeEmailDataResolver;

    /**
     * @var ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param LoggerInterface $logger
     * @param BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver
     * @param ConfigInterface $productTypeConfig
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        Json $json,
        LoggerInterface $logger,
        BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver,
        ConfigInterface $productTypeConfig
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->logger = $logger;
        $this->brandRepresentativeEmailDataResolver = $brandRepresentativeEmailDataResolver;
        $this->productTypeConfig = $productTypeConfig;
        parent::__construct($context);
    }

    /**
     * Get representative emails from product with input region id
     *
     * @param Product $product
     * @param int $regionId
     * @return string
     * @SuppressWarnings(CyclomaticComplexity)
     */
    public function extractRepresentativeEmail(Product $product, int $regionId): string
    {
        if ($regionId === 0) {
            return '[]';
        }
        $categoryIds = $product->getCategoryIds();
        try {
            if ($this->storeManager->getStore()) {
                $currentStoreId = $this->storeManager->getStore()->getId();
            } else {
                $currentStoreId = null;
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            return '';
        }
        $emailList = [];
        foreach ($categoryIds as $categoryId) {
            /* @var Category $category */
            try {
                $category = $this->categoryRepository->get($categoryId, $currentStoreId);
                $brandRepresentativeEmailData = $this->brandRepresentativeEmailDataResolver->execute(
                    $category->getId(),
                    1
                );
                if ($brandRepresentativeEmailData) {
                    foreach ($brandRepresentativeEmailData['bss_brand_representative_email'] as $emailData) {
                        if (isset($emailData['bss_province']) &&
                            in_array((string)$regionId, $emailData['bss_province'], true)
                        ) {
                            $categoryEmails = explode(',', $emailData['bss_email']);
                            $this->uniqueEmailList($emailList, $categoryEmails);
                            $this->pushToEmailList($emailList[$categoryId], $categoryEmails);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
                return '';
            }
        }
        return $this->json->serialize($emailList);
    }

    /**
     * Push to email list
     *
     * @param array $emailList
     * @param array $needPushEmails
     */
    protected function pushToEmailList(&$emailList, $needPushEmails)
    {
        if ($emailList === null) {
            $emailList = [];
        }
        $emailList = array_merge($emailList, $needPushEmails);
    }

    /**
     * Remove duplicate brand email
     *
     * @param array $list
     * @param array $mailData
     */
    protected function uniqueEmailList($list, &$mailData)
    {
        foreach ($mailData as $index => $email) {
            foreach ($list as $existedEmails) {
                foreach ($existedEmails as $existedEmail) {
                    if ($email == $existedEmail) {
                        unset($mailData[$index]);
                    }
                }
            }
        }
    }

    /**
     * Get config featured brands
     *
     * @param int|null $storeId
     * @return array
     */
    public function getFeaturedBrandIds($storeId = null)
    {
        $result = $this->scopeConfig->getValue(
            self::FEATURED_BRANDS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($result) {
            return explode(",", $result);
        }

        return [];
    }

    /**
     * Get sales email sender
     *
     * @param int|null $storeId
     * @return string
     */
    public function getSalesEmailSender($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::SALES_EMAIL_SENDER_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sales sender name
     *
     * @param null $storeId
     * @return string
     */
    public function getSalesSenderName($storeId= null)
    {
        $senderName = $this->scopeConfig->getValue(
            self::SALES_SENDER_NAME_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$senderName) {
            $senderName = __("Sales Support");
        }

        return $senderName;
    }

    /**
     * Get all product types
     *
     * @return array
     */
    public function getAllProductTypes(): array
    {
        $options = [];

        try {
            foreach ($this->productTypeConfig->getAll() as $productTypeData) {
                $options[$productTypeData['name']] = $productTypeData['label'];
            }
        } catch (\Exception $e) {
            $this->_logger->critical(
                self::class . "::getAllProductTypes(): " .
                $e
            );
        }

        return $options;
    }
}
