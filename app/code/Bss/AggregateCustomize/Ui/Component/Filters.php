<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

/**
 * Class Filters
 */
class Filters extends \Magento\Ui\Component\Filters
{
    /**
     * Remove Store Views filter section
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        if (isset($this->components["store_id"])) {
            unset($this->components["store_id"]);
        }
    }
}
