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

namespace Bss\LatestProductListing\Block\Product;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\View\Element\Template;

/**
 * Listing product
 */
class ListNew extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Layer
     */
    protected $catalogLayer;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * @var \Smartwave\Porto\Helper\Data
     */
    protected $portoHelper;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;
    /**
     * @var \Smartwave\Dailydeals\Helper\Data
     */
    protected $dailyHelper;
    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $catalogHelperOutput;

    /**
     * ListNew constructor.
     *
     * @param Resolver $resolver
     * @param \Smartwave\Porto\Helper\Data $portoHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Smartwave\Dailydeals\Helper\Data $dailyHelper
     * @param \Magento\Catalog\Helper\Output $catalogHelperOutput
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Resolver $resolver,
        \Smartwave\Porto\Helper\Data $portoHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Smartwave\Dailydeals\Helper\Data $dailyHelper,
        \Magento\Catalog\Helper\Output $catalogHelperOutput,
        Template\Context $context,
        array $data = []
    ) {
        $this->catalogLayer = $resolver->get();
        $this->portoHelper = $portoHelper;
        $this->imageHelper = $imageHelper;
        $this->dailyHelper = $dailyHelper;
        $this->catalogHelperOutput = $catalogHelperOutput;
        parent::__construct($context, $data);
    }

    public function getLoadedProductCollection()
    {
        return $this->getProductCollection();
    }

    public function getProductCollection()
    {
        if ($this->productCollection === null) {
            $this->productCollection = $this->initializeProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * Get catalog helper output object
     *
     * @return \Magento\Catalog\Helper\Output
     */
    public function getCatalogHelperOutput(): \Magento\Catalog\Helper\Output
    {
        return $this->catalogHelperOutput;
    }

    /**
     * Get daily helper object
     *
     * @return \Smartwave\Dailydeals\Helper\Data
     */
    public function getDailyHelper(): \Smartwave\Dailydeals\Helper\Data
    {
        return $this->dailyHelper;
    }

    /**
     * Get image hhelper object
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper(): \Magento\Catalog\Helper\Image
    {
        return $this->imageHelper;
    }

    /**
     * Get porto helper object
     *
     * @return \Smartwave\Porto\Helper\Data
     */
    public function getPortoHelper(): \Smartwave\Porto\Helper\Data
    {
        return $this->portoHelper;
    }

    /**
     * Init product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function initializeProductCollection(): \Magento\Catalog\Model\ResourceModel\Product\Collection
    {
        $layer = $this->getLayer();

        $collection = $layer->getProductCollection();
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $collection]
        );

        return $collection;
    }

    /**
     * Get layer
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer(): \Magento\Catalog\Model\Layer
    {
        return $this->catalogLayer;
    }

    /**
     * Get listing mode
     *
     * @return string
     */
    public function getMode(): string
    {
        return "grid";
    }
}
