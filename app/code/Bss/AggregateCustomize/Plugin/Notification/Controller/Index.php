<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Notification\Controller;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\AdminNotification\Controller\Adminhtml\Notification\Index as BePlugged;

/**
 * Class Index
 * Disable notification feature
 *
 * @see BePlugged
 */
class Index
{
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Index constructor.
     *
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        RedirectFactory $redirectFactory
    ) {
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Disable notification feature
     *
     * @param BePlugged $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        BePlugged $subject,
        callable $proceed
    ) {
        return $this->redirectFactory->create()->setPath("*/");
    }
}
