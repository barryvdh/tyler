<?php
declare(strict_types=1);

namespace Bss\HideProductField\Plugin\Product;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Downloadable\Model\Product\Type as DownloadableType;

/**
 * Class RestrictDigitalManger
 * Prevent create/edit not dơnloadable product for digital manager
 */
class RestrictDigitalManger
{
    /**
     * @var \Bss\AggregateCustomize\Helper\Data
     */
    private $aggregateCustomizeHelper;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * NewAction constructor.
     *
     * @param \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        \Bss\AggregateCustomize\Helper\Data $aggregateCustomizeHelper,
        RedirectFactory $redirectFactory
    ) {
        $this->aggregateCustomizeHelper = $aggregateCustomizeHelper;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * Restrict brand manager create or edit product khong phai la downloadable
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Adminhtml\Product $subject,
        $proceed
    ) {
        if ($this->aggregateCustomizeHelper->isBrandManager() &&
            $subject->getRequest()->getParam('type') !== DownloadableType::TYPE_DOWNLOADABLE
        ) {
            return $this->redirectFactory->create()->setPath("catalog/*/");
        }

        return $proceed();
    }
}
