<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Ui\Component\Customer\Form;

use Magento\Framework\View\Element\ComponentVisibilityInterface;

/**
 * Class OrderRestrictionFieldset
 *
 * Component visibility: if the current customer is not sub-user
 */
class OrderRestrictionFieldset implements ComponentVisibilityInterface
{

    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        return true;
    }
}
