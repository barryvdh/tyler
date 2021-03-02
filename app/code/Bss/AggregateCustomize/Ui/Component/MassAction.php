<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

/**
 * Class MassAction
 */
class MassAction extends \Magento\Catalog\Ui\Component\Product\MassAction
{
    /**
     * Keep exist on mass action
     *
     * @var string[]
     */
    private $protectedActions = ["delete", "status"];

    /**
     * Remove all options in the dropdown except Delete and ‘Change status’
     *
     * @return void
     */
    public function prepare(): void
    {
        parent::prepare();
        $config = $this->getConfiguration();
        if (isset($config['actions'])) {
            foreach ($config['actions'] as $index => $action) {
                if (!in_array($action["type"], $this->protectedActions)) {
                    unset($config['actions'][$index]);
                }
            }
            $this->setData("config", $config);
        }
    }
}
