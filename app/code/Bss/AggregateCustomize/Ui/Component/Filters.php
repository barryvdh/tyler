<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Filters
 * Logic for brand manager
 */
class Filters extends \Magento\Ui\Component\Filters
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * Filters constructor.
     *
     * @param Data $helper
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $helper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Remove Store Views filter section
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->helper->isBrandManager() && isset($this->components["store_id"])) {
            unset($this->components["store_id"]);
        }
    }
}
