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
 * @package    Bss_BrandCategoryLevel
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandCategoryLevel\Observer\Category;

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\Session;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BrandInfo, observer that modify data if category belong to a brand level
 */
class BrandInfo implements ObserverInterface
{
    /**
     * Category as Brand level
     */
    public const CATEGORY_BRAND_LEVEL = 3;

    /**
     * @var Session
     */
    protected $categorySession;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * BrandInfo constructor.
     * @param Session $categorySession
     * @param Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct
    (
        Session $categorySession,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->categorySession = $categorySession;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /* @var Category $currentCategory */
        $currentCategory = $observer->getCategory();
        if ($currentCategory->getLevel() >= 4) {
            try {
                $brandCategoryId = $this->findBrand($currentCategory->getParentCategories());
                if ($brandCategoryId !== 0) {
                    $brandCategory = $this->categoryRepository
                        ->get($brandCategoryId, $this->storeManager->getStore()->getId());
                    if (!empty($brandCategory)) {
                        $currentCategory->setOriginalName($currentCategory->getName());
                        $currentCategory->setImage($brandCategory->getImage());
                        $currentCategory->setName($brandCategory->getName());
                        $currentCategory->setDescription($brandCategory->getDescription());
                        $currentCategory->setShortDescription($brandCategory->getShortDescription());
                        $brandCover = $brandCategory->getCustomAttribute("cover_category");
                        if ($brandCover) {
                            $currentCategory->setCustomAttribute(
                                "cover_category",
                                $brandCover->getValue()
                            );
                            $currentCategory->setCoverCategory($brandCategory->getCoverCategory());
                        }
                        $this->categorySession->setLastVisitedCategoryId($brandCategory->getId());
                        $this->registry->register('current_category', $brandCategory);
                    }
                }
            } catch (Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
    }

    /**
     * @param $categories
     * @return int
     */
    public function findBrand($categories): int
    {
        /* @var Category $category */
        foreach ($categories as $category) {
            if ((int)$category->getLevel() == self::CATEGORY_BRAND_LEVEL) {
                return $category->getId();
            }
        }
        return 0;
    }
}
