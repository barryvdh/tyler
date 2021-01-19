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
     * Data constructor.
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->logger = $logger;
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
                $categoryData = $category->getData();
                if (isset($categoryData['bss_brand_representative_email'])) {
                    $categoryDataEmail = $this->json->unserialize($categoryData['bss_brand_representative_email']);
                    foreach ($categoryDataEmail as $emailData) {
                        if (isset($emailData['bss_province']) &&
                            in_array((string)$regionId, $emailData['bss_province'], true)
                        ) {
                            $emailList[$categoryId] = explode(',', $emailData['bss_email']);
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
}
