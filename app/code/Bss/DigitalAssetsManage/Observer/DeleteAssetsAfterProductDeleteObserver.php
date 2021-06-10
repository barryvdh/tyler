<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Observer;

use Bss\DigitalAssetsManage\Helper\DownloadableHelper;
use Bss\DigitalAssetsManage\Model\DigitalImageProcessor;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteAssetsAfterProductDeleteObserver
 * Delete all downloadable link after product delete
 */
class DeleteAssetsAfterProductDeleteObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DownloadableHelper
     */
    protected $downloadableHelper;

    /**
     * @var DigitalImageProcessor
     */
    protected $digitalImageProcessor;

    /**
     * DeleteAssetsAfterProductDeleteObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param DownloadableHelper $downloadableHelper
     * @param DigitalImageProcessor $digitalImageProcessor
     */
    public function __construct(
        LoggerInterface $logger,
        DownloadableHelper $downloadableHelper,
        DigitalImageProcessor $digitalImageProcessor
    ) {
        $this->logger = $logger;
        $this->downloadableHelper = $downloadableHelper;
        $this->digitalImageProcessor = $digitalImageProcessor;
    }

    /**
     * Delete all downloadable links assets from the brand dir
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getProduct();

        try {
            $brandPath = $product->getBrandPath();
            if (!$brandPath) {
                return;
            }

            $links = $product->getNeedDeleteDownloadableProductLinks();
            $samples = $product->getNeedDeleteDownloadableProductSamples();
            $galleries = $product->getNeedDeleteGalleryEntries();
            $this->downloadableHelper->deleteLink($links);
            $this->downloadableHelper->deleteLink($samples);
            $this->digitalImageProcessor->deleteGalleryImages($galleries);
        } catch (\Exception $e) {
            $this->logger->critical(
                "BSS.ERROR: When delete downloadable link assets. " . $e
            );
        }
    }
}
