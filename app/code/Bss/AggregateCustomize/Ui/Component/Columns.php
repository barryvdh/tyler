<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Columns
 * Display specific columns for brand manager
 */
class Columns extends \Magento\Catalog\Ui\Component\Listing\Columns
{
    /**
     * @var Data
     */
    private $helper;

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
     * @param Data $helper
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory
     * @param \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Ui\Component\ColumnFactory $columnFactory,
        \Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $columnFactory, $attributeRepository, $components, $data);
    }

    /**
     * Rm all columns in grid except columns in $protectedFields variable
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->helper->isBrandManager()) {
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
