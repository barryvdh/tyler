<?php
/**
 * Class for Restrictcustomergroup ListCategories
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class ListCategories implements OptionSourceInterface
{

    /**
     * @var \FME\SeoMetaTagsGenerator\Model\Metatemplate
     */
    protected $_categoryHelper;
    protected $categoryFlatConfig;
    /**
     * Constructor
     *
     * @param \FME\SeoMetaTagsGenerator\Model\Metatemplate $_seometatagsgeneratorgroupRule
     */
    public function __construct( \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState)
    {
        $this->_categoryHelper = $categoryHelper;
        $this->categoryFlatConfig = $categoryFlatState;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {


        $options = [];
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryCollection->create();
        $categories->addAttributeToSelect('*');

        foreach ($categories as $category) {
            $options[] = [
                        'label' => str_repeat("--- ",$category->getLevel()).$category->getName(),
                        'value' => $category->getId(),
                    ];
        }

        unset($options[0]);

        return $options;
    }

}
