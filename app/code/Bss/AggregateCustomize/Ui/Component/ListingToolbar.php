<?php
declare(strict_types=1);

namespace Bss\AggregateCustomize\Ui\Component;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class ListingToolbar
 */
class ListingToolbar extends \Magento\Ui\Component\Container
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ListingToolbar constructor.
     *
     * @param Data $helper
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $helper,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $components, $data);
    }

    /**
     * Remove the columns control and bookmark in grid for the Brand Manager user
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->helper->isBrandManager()) {
            unset($this->components['columns_controls']);
            unset($this->components['bookmarks']);
        }
    }
}
