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

namespace Bss\BrandCategoryLevel\ViewModel\Category;

use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Output View model
 */
class Output implements ArgumentInterface
{
    const BRAND_CATEGORY_LV = 3;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OutputHelper
     */
    protected $output;

    /**
     * @var \Magento\Catalog\Model\Category\Image
     */
    private $categoryImage;

    /**
     * Output constructor.
     *
     * @param Category\Image $categoryImage
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param OutputHelper $output
     */
    public function __construct(
        \Magento\Catalog\Model\Category\Image $categoryImage,
        Registry $registry,
        StoreManagerInterface $storeManager,
        OutputHelper $output
    ) {
        $this->categoryImage = $categoryImage;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->output = $output;
    }

    /**
     * @return Category\Image
     */
    public function getCategoryImage()
    {
        return $this->categoryImage;
    }

    /**
     * @return \Magento\Catalog\Model\Category|null
     */
    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * Fetch all child Categories
     *
     * @return array|Category[]|Collection
     * @throws LocalizedException
     */
    public function getChildCategories()
    {
        /* @var Category $currentCategory*/
        $currentCategory = $this->getCurrentCategory();
        if ($currentCategory) {
            return $currentCategory->getChildrenCategories()
                ->addAttributeToSelect('image');
        }
        return [];
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function prepareBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @param string $configPath
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig(string $configPath)
    {
        return $this->storeManager->getStore()->getConfig($configPath);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function preparePlaceholder(): string
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl . 'catalog/product/placeholder/' .
            $this->getConfig('catalog/placeholder/image_placeholder');
    }

    /**
     * @return OutputHelper
     */
    public function getCatalogOutputHelper(): OutputHelper
    {
        return $this->output;
    }

    /**
     * Get sub brand of current category
     *
     * @return Category
     * @throws LocalizedException
     */
    public function getSubBrandOfCurrentBrand()
    {
        $currentCategory = $this->getCurrentCategory();
        if ($currentCategory->getChildrenCount() == 1) {
            foreach ($this->getChildCategories() as $childCate) {
                return $childCate;
            }
        }

        return null;
    }

    /**
     * Get template for brand category
     *
     * @param string $defaultTmpl
     * @return string
     */
    public function getBrandTemplate($defaultTmpl)
    {
        // if ($this->getCurrentCategory()->getLevel() != self::BRAND_CATEGORY_LV) {
        //     return $defaultTmpl;
        // }

        return 'Bss_BrandCategoryLevel::product/list.phtml';
    }

    /**
     * Add brand list
     *
     * @param \Magento\Framework\View\Element\Template $parentBlock
     */
    public function addChildrenBrandBlock($parentBlock)
    {
        $parentBlock->addChild(
            'sub_brand_list',
            \Magento\Framework\View\Element\Template::class,
            ['template' => "Bss_BrandCategoryLevel::brand/list.phtml"]
        )
            ->setListBrandCategories($this->getChildCategories())
            ->setViewModel($this);
    }
}
