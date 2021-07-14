<?php
declare(strict_types=1);
namespace Bss\ProductInventoryReport\Block\Adminhtml\Report\Filter;

/**
 * Class Form
 * Modify filter form
 */
class Form extends \Magento\Sales\Block\Adminhtml\Report\Filter\Form
{
    const BRAND_LV = 3;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Sales\Model\Order\ConfigFactory $orderConfig
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Sales\Model\Order\ConfigFactory $orderConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $orderConfig, $data);
    }

    /**
     * Add brands field for filter
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $fieldset->addField(
            'brands',
            'multiselect',
            [
                'name' => 'brands',
                'label' => __('Brands'),
                'values' => $this->getBrandOptions(),
                'title' => __('Select brand(s) for filter.')
            ]
        );
        return $this;
    }

    /**
     * Get brand filter options
     *
     * @return array
     */
    protected function getBrandOptions(): array
    {
        $options = [];
        try {
            /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
            $categories = $this->categoryCollectionFactory->create();
            $categories->addAttributeToSelect(['entity_id', 'name'])
                ->addAttributeToFilter('level', ['eq' => static::BRAND_LV]);

            foreach ($categories as $category) {
                $options[] = [
                    'label' => $category->getName(),
                    'value' => $category->getId()
                ];
            }
        } catch (\Exception $e) {
            $this->_logger->critical($e);
        }

        return $options;
    }
}
