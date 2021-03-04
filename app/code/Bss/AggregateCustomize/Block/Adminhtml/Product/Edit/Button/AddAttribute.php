<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml\Product\Edit\Button;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\AddAttribute as Generic;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

/**
 * Class AddAttribute
 * Remove the add attribute button with brand manager user
 */
class AddAttribute extends Generic
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * AddAttribute constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $helper
     */
    public function __construct(Context $context, Registry $registry, Data $helper)
    {
        $this->helper = $helper;
        parent::__construct($context, $registry);
    }

    /**
     * Remove the add attribute button if user is sale rep
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->helper->isBrandManager()) {
            return [];
        }

        return parent::getButtonData();
    }
}
