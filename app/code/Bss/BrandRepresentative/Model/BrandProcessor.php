<?php
declare(strict_types=1);
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
 * @package    Bss_LatestProductListing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandRepresentative\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;

/**
 * Processor for brand
 */
class BrandProcessor
{
    const BRAND_LV = 3;

    /**
     * @var Category[]
     */
    protected $brandIdData;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * BrandProcessor constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get brand id recursive
     *
     * @param int $categoryId
     * @return null|Category
     */
    public function getBrand(int $categoryId): ?Category
    {
        return $this->getBrandRecursive($categoryId, $categoryId);
    }

    /**
     * Get brand recursive
     *
     * @param int|Category $category
     * @param int|Category $startRecursiveCate mark start find brand id to save to local
     * @return null|Category
     */
    public function getBrandRecursive($category, $startRecursiveCate): ?Category
    {
        if (!is_object($category)) {
            $category = $this->categoryRepository->get($category);
        }

        if (!is_object($startRecursiveCate)) {
            $startRecursiveCate = $this->categoryRepository->get($startRecursiveCate);
        }

        if (isset($this->brandIdData[$startRecursiveCate->getId()])) {
            return $this->brandIdData[$startRecursiveCate->getId()];
        }

        if (!$category->getId()) {
            return null;
        }

        if ((int) $category->getLevel() === static::BRAND_LV) {
            $this->brandIdData[$startRecursiveCate->getId()] = $category;

            return $category;
        }

        if ($category->getLevel() > static::BRAND_LV) {
            return $this->getBrandRecursive($category->getParentCategory(), $startRecursiveCate);
        }

        return null;
    }
}
