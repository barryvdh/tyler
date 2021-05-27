<?php
declare(strict_types=1);

namespace Bss\DigitalAssetsManage\Block\Adminhtml\Report;

use Bss\DigitalAssetsManage\Helper\UniqueFileName;
use Magento\Framework\Data\Collection;

/**
 * Class Grid
 * Brand digital assets grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set brand colleciton to report
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection($this->getBrandDigitalAssetsStorageCollection());
        return parent::_prepareCollection();
    }

    /**
     * Avoid error in line 246 in extended.phtml template
     *
     * @param \Magento\Framework\DataObject $item
     * @return false
     */
    public function getMultipleRows($item)
    {
        return false;
    }

    /**
     * Get brand collection
     *
     * @return Collection
     */
    protected function getBrandDigitalAssetsStorageCollection()
    {
        $brands = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')->addFieldToFilter("level", ['eq' => 3]);

        return $brands;
    }

    /**
     * Prepare report grid cols
     *
     * @return Grid
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __("Brand ID"),
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Brand Name'),
                'index' => 'name'
            ]
        );

        $this->addColumn(
            'storage_amount',
            [
                'header' => __("Storage Amount"),
                'index' => 'storage_amount',
                'sortable' => false,
                'frame_callback' => [$this, "storageCalculate"]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Calculate the size of brand assets
     *
     * @param string $value
     * @param \Magento\Catalog\Model\Category $category
     * @param \Magento\Backend\Block\Widget\Grid\Column\Extended $column
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function storageCalculate($value, $category, $column)
    {
        if ($category instanceof \Magento\Catalog\Model\Category) {
            $size = $this->folderSize(
                $this->getMediaDirectory()->getAbsolutePath(),
                $category->getName()
            );
            return round($size / 1024 / 1024, 1) . " MB";
        }

        return 0 . "MB";
    }

    /**
     * Get folder size of brand dir
     *
     * @param string $dir
     * @param string $brandName
     * @return false|int
     */
    protected function folderSize($dir, $brandName)
    {
        $size = 0;

        // @codingStandardsIgnoreStart
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
//            dump($each, is_file($each) && strpos(
//                    $each,
//                    $brandName . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME
//                ) !== false, ['strpos ' => [$each,
//                $brandName . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME]]);
            if (is_file($each) &&
                strpos(
                    $each,
                    $brandName . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME
                ) !== false
            ) {
//                dump(['filesize' => $this->getMediaDirectory()->stat($each)]);
//                vadu_log(['sizeee_nme' => $each]);
                $size += filesize($each);
            } else {
                $size += $this->folderSize($each, $brandName);
            }
        }

        return $size;
    }

    /**
     * Disable filter
     *
     * @return false
     */
    public function getFilterVisibility()
    {
        return false;
    }
}
