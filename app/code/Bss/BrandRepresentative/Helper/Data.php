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
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * Data constructor.
     *
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param LoggerInterface $logger
     * @param BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        Json $json,
        LoggerInterface $logger,
        BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->logger = $logger;
        $this->brandRepresentativeEmailDataResolver = $brandRepresentativeEmailDataResolver;
        parent::__construct($context);
    }

    /**
     * @param Product $product
     * @param int $regionId
     * @return string
     */
    public function extractRepresentativeEmail(Product $product, int $regionId): string
    {
        $categoryIds = $product->getCategoryIds();
        $currentStoreId = null;
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
                            $emailList[$categoryId] = $categoryEmails;
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
}
