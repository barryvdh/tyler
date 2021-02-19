<?php
namespace Bss\CategoryAttributes\ViewModel\Category;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class Output implements ArgumentInterface
{
    /**
     * @var \Magento\Catalog\Model\Category\Image
     */
    private $categoryImage;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    private $catalogOutputHelper;

    /**
     * Output constructor.
     *
     * @param \Magento\Catalog\Model\Category\Image $categoryImage
     * @param \Magento\Catalog\Helper\Output $catalogOutputHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Category\Image $categoryImage,
        \Magento\Catalog\Helper\Output $catalogOutputHelper
    ) {
        $this->categoryImage = $categoryImage;
        $this->catalogOutputHelper = $catalogOutputHelper;
    }

    /**
     * Get category image
     *
     * @return \Magento\Catalog\Model\Category\Image
     */
    public function getCategoryImage()
    {
        return $this->categoryImage;
    }

    /**
     * Get catalog ouput helper object
     *
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogOutputHelper()
    {
        return $this->catalogOutputHelper;
    }
}
