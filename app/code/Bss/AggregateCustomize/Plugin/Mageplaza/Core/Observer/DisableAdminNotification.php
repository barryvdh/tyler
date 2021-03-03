<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Mageplaza\Core\Observer;

/**
 * Class DisableAdminNotification
 * Disable mageplaza notification checking when module notification be disabled
 */
class DisableAdminNotification
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * DisableAdminNotification constructor.
     *
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Disable function if Magento_AdminNotification be disabled
     *
     * @param \Mageplaza\Core\Observer\PredispatchAdminActionControllerObserver $subject
     * @param callable $proceed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        \Mageplaza\Core\Observer\PredispatchAdminActionControllerObserver $subject,
        $proceed
    ) {
        if ($this->moduleManager->isEnabled("Magento_AdminNotification")) {
            $proceed();
        }
    }
}
