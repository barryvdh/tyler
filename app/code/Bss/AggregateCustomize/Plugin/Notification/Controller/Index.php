<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Notification\Controller;

use Bss\AggregateCustomize\Helper\Data;
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
     * @var Data
     */
    private $helper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Index constructor.
     *
     * @param Data $helper
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Data $helper,
        RedirectFactory $redirectFactory
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->helper = $helper;
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
        if (!$this->helper->isBrandManager()) {
            return $proceed();
        }
        return $this->redirectFactory->create()->setPath("*/");
    }
}
