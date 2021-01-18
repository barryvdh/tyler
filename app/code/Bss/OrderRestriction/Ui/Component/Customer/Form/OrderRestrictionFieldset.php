<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Ui\Component\Customer\Form;

use Magento\Framework\View\Element\ComponentVisibilityInterface;
use Magento\Ui\Component\Form\Fieldset;

/**
 * Class OrderRestrictionFieldset
 *
 * Component visibility: if the current customer is not sub-user
 */
class OrderRestrictionFieldset extends Fieldset implements ComponentVisibilityInterface
{

    /**
     * @inheritDoc
     */
    public function isComponentVisible(): bool
    {
        return true;
    }
}
