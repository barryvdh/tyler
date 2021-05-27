<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Ui\Component;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

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
     * @var Data
     */
    private $helper;

    /**
     * MassAction constructor.
     *
     * @param Data $helper
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     */
    public function __construct(
        Data $helper,
        AuthorizationInterface $authorization,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($authorization, $context, $components, $data);
    }

    /**
     * Remove all options in the dropdown except Delete and ‘Change status’
     *
     * @return void
     */
    public function prepare(): void
    {
        parent::prepare();
        if (!$this->helper->isBrandManager()) {
            return;
        }
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
