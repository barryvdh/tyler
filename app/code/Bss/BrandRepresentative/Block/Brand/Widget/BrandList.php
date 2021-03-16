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

namespace Bss\BrandRepresentative\Block\Brand\Widget;

use Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Bss\BrandRepresentative\Helper\Data as Helper;

/**
 * Class BrandList
 *
 * Bss\BrandRepresentative\Block\Brand\Widget
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandList extends \Bss\BrandRepresentative\Block\Brand\Pages\BrandList implements BlockInterface
{
    /**
     * Default widget limit
     */
    const DEFAULT_FEATURE_BRANDS_LIMIT = 10;

    /**
     * Default featured widget title
     */
    const PAGE_DEFAULT_FEATURE_TITLE = 'Featured Brands';

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Magento\Catalog\Model\Category\Image
     */
    private $categoryImage;

    /**
     * @var OutputHelper
     */
    private $outputHelper;

    /**
     * BrandList constructor.
     *
     * @param Conditions $conditionsHelper
     * @param ImageFactory $helperImageFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Context $context
     * @param Toolbar $brandToolbar
     * @param \Magento\Catalog\Model\Category\Image $categoryImage
     * @param OutputHelper $outputHelper
     * @param StoreManagerInterface $storeManager
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Conditions $conditionsHelper,
        ImageFactory $helperImageFactory,
        CollectionFactory $categoryCollectionFactory,
        Template\Context $context,
        Toolbar $brandToolbar,
        \Magento\Catalog\Model\Category\Image $categoryImage,
        OutputHelper $outputHelper,
        StoreManagerInterface $storeManager,
        Helper $helper,
        array $data = []
    ) {
        $this->conditionsHelper = $conditionsHelper;
        $this->categoryImage = $categoryImage;
        $this->outputHelper = $outputHelper;
        parent::__construct(
            $helperImageFactory,
            $categoryCollectionFactory,
            $context,
            $brandToolbar,
            $categoryImage,
            $outputHelper,
            $storeManager,
            $helper,
            $data
        );
    }

    /**
     * Get the widget title
     *
     * @return array|mixed|null
     */
    public function getTitle()
    {
        if (!$this->hasData('title')) {
            $this->setData('title', self::PAGE_DEFAULT_FEATURE_TITLE);
        }
        return $this->getData('title');
    }

    /**
     * Get list category follow widget config
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection|null
     */
    public function getCategories()
    {
        try {
            $categoryIds = $this->getCategoryIds();
            $categoryCollection = $this->prepareBrandCollection();

            if ($categoryCollection && !empty($categoryIds)) {
                $categoryCollection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);
            }

            return $categoryCollection;
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return null;
    }

    /**
     * No pagination, so set current page is always 1
     *
     * @return int
     */
    protected function getCurPage()
    {
        return 1;
    }

    /**
     * Retrieve category ids from widget
     *
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->helper->getFeaturedBrandIds();
    }

    /**
     * Get widget page size
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->getData('page_size') ?: self::DEFAULT_FEATURE_BRANDS_LIMIT;
    }
}
