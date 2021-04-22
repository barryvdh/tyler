<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Columns
 * Display specific columns for brand manager
 */
class Columns extends \Bss\ProductGridInlineEditor\Ui\Component\Listing\Columns
{
    /**
     * @var Data
     */
    protected $aggregateHelper;

    /**
     * Visible columns in grid
     *
     * @var string[]
     */
    private $protectedFields = [
        "ids",
        "entity_id",
        "thumbnail",
        "name",
        "sku",
        "status",
        "short_description",
        "actions"
    ];

    /**
     * Columns constructor.
     *
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param \Magento\Framework\View\Element\UiComponentFactory $componentFactory
     * @param \Bss\ProductGridInlineEditor\Helper\Data $productGridHelper
     * @param \Magento\Framework\App\Request\Http $request
     * @param Data $helper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        \Magento\Framework\View\Element\UiComponentFactory $componentFactory,
        \Bss\ProductGridInlineEditor\Helper\Data $productGridHelper,
        \Magento\Framework\App\Request\Http $request,
        Data $helper,
        array $components = [],
        array $data = []
    ) {
        $this->aggregateHelper = $helper;
        parent::__construct(
            $context,
            $columnFactory,
            $attributeRepository,
            $componentFactory,
            $productGridHelper,
            $request,
            $components,
            $data
        );
    }

    /**
     * Rm all columns in grid except columns in $protectedFields variable
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->aggregateHelper->isBrandManager()) {
            return;
        }
        /**
         * @var string $name
         * @var UiComponentInterface $object
         */
        foreach ($this->components as $name => $object) {
            if (!in_array($name, $this->protectedFields)) {
                unset($this->components[$name]);
                continue;
            }
            // Force visible the component
            if ($objectConfig = $object->getData('config')) {
                if (isset($objectConfig['visible']) && $objectConfig['visible'] == false) {
                    $objectConfig['visible'] = true;
                    $object->setData('config', $objectConfig);
                }
            }
        }
    }
}
