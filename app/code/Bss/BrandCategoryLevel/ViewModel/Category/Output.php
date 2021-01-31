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

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Class Output
 * @package Bss\BrandCategoryLevel\ViewModel\Category
 */
class Output implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var BrandList
     */
    protected $bssBrand;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Output constructor.
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed|null
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
        $mediaUrl = $this ->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA );
        return $mediaUrl . 'catalog/product/placeholder/' .
            $this->getConfig('catalog/placeholder/image_placeholder');
    }
}
