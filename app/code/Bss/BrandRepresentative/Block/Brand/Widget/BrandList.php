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

use Magento\Catalog\Helper\ImageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Bss\BrandRepresentative\Block\Brand\Pages\BrandList\Toolbar;

/**
 * Class BrandList
 *
 * Bss\BrandRepresentative\Block\Brand\Widget
 * @suppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BrandList extends \Bss\BrandRepresentative\Block\Brand\Pages\BrandList implements BlockInterface
{
    const BRAND_CATEGORY = 3;
    const DEFAULT_FEATURE_BRANDS_LIMIT = 10;

    /**
     * @var Conditions
     */
    protected $conditionsHelper;

    //Default page size value
    public const PAGE_DEFAULT_FEATURE_TITLE = 'Featured Brands';

    /**
     * BrandList constructor.
     *
     * @param Conditions $conditionsHelper
     * @param ImageFactory $helperImageFactory
     * @param AdapterFactory $adapterFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Context $context
     * @param Toolbar $brandToolbar
     * @param array $data
     */
    public function __construct(
        Conditions $conditionsHelper,
        ImageFactory $helperImageFactory,
        AdapterFactory $adapterFactory,
        CollectionFactory $categoryCollectionFactory,
        Template\Context $context,
        Toolbar $brandToolbar,
        array $data = []
    ) {
        $this->conditionsHelper = $conditionsHelper;
        parent::__construct(
            $helperImageFactory,
            $adapterFactory,
            $categoryCollectionFactory,
            $context,
            $brandToolbar,
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
     * @inheritDoc
     */
    public function getCategories()
    {
        try {
            $categoryIds = $this->getCategoryIds();
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
            $categoryCollection = $this->categoryCollectionFactory->create()
                ->setStore($this->_storeManager->getStore());
            $categoryCollection->addFieldToSelect('*');
            $categoryCollection->addAttributeToFilter([
                [
                    'attribute' => 'level',
                    'eq' => self::BRAND_CATEGORY
                ]
            ]);

            if (!empty($categoryIds)) {
                $ids = explode(',', $categoryIds);
                $categoryCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
            }
            $categoryCollection->setPageSize($this->getPageSize());

            return $categoryCollection;
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical($e->getMessage());
        }
        return [];
    }

    /**
     * Retrieve category ids from widget
     *
     * @return string
     */
    public function getCategoryIds()
    {
        $conditions = $this->getData('conditions') ?: $this->getData('conditions_encoded') ?? [];

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        foreach ($conditions as $condition) {
            if (!empty($condition['attribute']) && $condition['attribute'] === 'category_ids') {
                return $condition['value'];
            }
        }
        return '';
    }

    /**
     * Get widget page size
     *
     * @return int
     */
    private function getPageSize()
    {
        return $this->getData('page_size') ?: self::ALL_BRAND_PAGE_SIZE;
    }
}
