<?php
/**
 * Class for Restrictcustomergroup IsActive
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Model\Rule\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
{

    /**
     * @var \FME\Restrictcustomergroup\Model\Rule
     */
    protected $_restrictcustomergroupRule;

    /**
     * Constructor
     *
     * @param \FME\Restrictcustomergroup\Model\Rule $_restrictcustomergroupRule
     */
    public function __construct(\FME\Restrictcustomergroup\Model\Rule $_restrictcustomergroupRule)
    {
        $this->_restrictcustomergroupRule = $_restrictcustomergroupRule;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_restrictcustomergroupRule->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }

        return $options;
    }
}
