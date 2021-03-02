<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\AddAttribute as Generic;

/**
 * Class AddAttribute
 */
class AddAttribute extends Generic
{
    /**
     * Remove the add attribute button if user is sale rep
     *
     * @return array
     */
    public function getButtonData()
    {
        return [];
    }
}
