<?php
declare(strict_types=1);
namespace Bss\BrandCategoryLevel\Block\Brand;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

/**
 * Class Breadcrumbs
 * Add custom bran breadcrumbs
 */
class Breadcrumbs extends \Magento\Theme\Block\Html\Breadcrumbs
{
    const BRAND_CATEGORY_LEVEL = 3;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Breadcrumbs constructor.
     *
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductRepositoryInterface $productRepository
     * @param Template\Context $context
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        ProductRepositoryInterface $productRepository,
        Template\Context $context,
        array $data = [],
        Json $serializer = null
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        parent::__construct($context, $data, $serializer);
    }

    /**
     * Execute brand breadcrumb
     */
    public function createBrandBreadcrumb()
    {
        $this->clearCrumb();

        // add root crumb
        $this->addCrumb(
            "brands",
            [
                "title" => __("Brands"),
                "label" => __("Brands"),
                "link" => $this->getUrl("brands")
            ]
        );
        $this->getCrumbChildren($this->getCategoryId());
    }

    /**
     * Get category id
     *
     * @return int|null
     */
    protected function getCategoryId()
    {
        $id = $this->getRequest()->getParam("id");
        if ($this->getRequest()->getFullActionName() === "catalog_product_view") {
            try {
                $product = $this->productRepository->getById($id);
                if (count($product->getCategoryIds()) > 1) {
                    return null;
                }

                return array_values($product->getCategoryIds())[0];
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }

        return $id;
    }

    /**
     * Add product name to breadcrumb
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function addProductBreadcrumb()
    {
        if ($this->_request->getFullActionName() === "catalog_product_view") {
            $product = $this->productRepository->getById($this->getRequest()->getParam('id'));
            $this->addCrumb(
                "product" . $product->getId(),
                [
                    "title" => $product->getName(),
                    "label" => $product->getName(),
                    "link" => ""
                ]
            );
        }
    }

    /**
     * Is category page
     *
     * @return bool
     */
    protected function isBrandPage()
    {
        return $this->getRequest()->getFullActionName() === "catalog_category_view";
    }

    /**
     * Get brand crumb
     *
     * @param int|null $categoryId
     */
    protected function getCrumbChildren($categoryId)
    {
        try {
            $category = $this->categoryRepository->get($categoryId);
            // If the category lv is below the brand lv
            if ($categoryId === null || (int)$category->getLevel() < static::BRAND_CATEGORY_LEVEL) {
                return;
            }
            if ($pId = $category->getParentId()) {
                $this->getCrumbChildren($pId);
            }
            $link = $this->isBrandPage() && $category->getId() === $this->_request->getParam("id") ?
                "" :
                $category->getUrl();
            $this->addCrumb(
                "category" . $category->getId(),
                [
                    "title" => $category->getName(),
                    "label" => $category->getName(),
                    "link" => $link
                ]
            );
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }
    }

    /**
     * Clear all current crumbs
     */
    protected function clearCrumb()
    {
        $this->_crumbs = [];
    }
}
