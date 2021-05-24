<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Plugin\Controller\Adminhtml\Product\Initialization;

use Bss\DigitalAssetsManage\Helper\UniqueFileName;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Downloadable\Helper\File;

/**
 * Class MoveDownloadableLinksToBrand
 * Move assets file to brand path for digital cate
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class MoveDownloadableLinksToBrand
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Downloadable\Model\LinkFactory
     */
    protected $linkFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var \Magento\Downloadable\Model\Link
     */
    protected $linkConfig;

    /**
     * @var \Magento\Downloadable\Model\Sample
     */
    protected $sampleConfig;

    /**
     * @var \Magento\Downloadable\Model\SampleFactory
     */
    protected $sampleFactory;

    /**
     * @var UniqueFileName
     */
    protected $uniqueFileName;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * MoveDownloadableLinksToBrand constructor.
     *
     * @param RequestInterface $request
     * @param \Magento\Downloadable\Model\LinkFactory $linkFactory
     * @param \Magento\Downloadable\Model\SampleFactory $sampleFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param File $file
     * @param UniqueFileName $uniqueFileName
     * @param \Psr\Log\LoggerInterface $logger
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        RequestInterface $request,
        \Magento\Downloadable\Model\LinkFactory $linkFactory,
        \Magento\Downloadable\Model\SampleFactory $sampleFactory,
        \Magento\Framework\Filesystem $filesystem,
        File $file,
        UniqueFileName $uniqueFileName,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->linkFactory = $linkFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->file = $file;
        $this->uniqueFileName = $uniqueFileName;
        $this->logger = $logger;
        $this->sampleFactory = $sampleFactory;
    }

    /**
     * Get link config object
     *
     * @return \Magento\Downloadable\Model\Link
     */
    public function getLink()
    {
        if (!$this->linkConfig) {
            $this->linkConfig = $this->linkFactory->create();
        }

        return $this->linkConfig;
    }

    /**
     * Get sample config object
     *
     * @return \Magento\Downloadable\Model\Sample
     */
    public function getSample()
    {
        if (!$this->sampleConfig) {
            $this->sampleConfig = $this->sampleFactory->create();
        }
        return $this->sampleConfig;
    }

    /**
     * Move assets file to brand path for digital cate
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings (UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getTypeId() === 'downloadable') {
            try {
                $extension = $product->getExtensionAttributes();
                $links = $extension->getDownloadableProductLinks();
                $samples = $extension->getDownloadableProductSamples();

                $brandPath = $this->uniqueFileName->getBrandDirectoryPath($product);

                if (!$brandPath) {
                    return $product;
                }

                $this->processProductLinks($links, $brandPath);
                $extension->setDownloadableProductLinks($links);


                $this->processProductSamples($samples, $brandPath);
                $extension->setDownloadableProductSamples($samples);

                $product->setExtensionAttributes($extension);
            } catch (\Exception $e) {
                $this->logger->critical(__("Error when move to brand directory: ") . $e);
            }
        }

        return $product;
    }

    /**
     * Processing downloadable product links
     *
     * @param \Magento\Downloadable\Model\Link[] $links
     * @param string $brandPath
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function processProductLinks($links, $brandPath)
    {
        if (!$links) {
            return;
        }

        foreach ($links as $link) {
            if ($linkFile = $link->getLinkFile()) {
                $newLinkFile = $this->getNewBrandFilePath(
                    $this->getLink()->getBasePath(),
                    $brandPath,
                    $linkFile
                );
                $link->setLinkFile($newLinkFile);
            }

            if ($sampleFile = $link->getSampleFile()) {
                $newSampleFile = $this->getNewBrandFilePath(
                    $this->getLink()->getBaseSamplePath(),
                    $brandPath,
                    $sampleFile
                );
                $link->setSampleFile($newSampleFile);
            }
        }
    }

    /**
     * Processing downloadable samples
     *
     * @param \Magento\Downloadable\Model\Sample[] $samples
     * @param string $brandPath
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function processProductSamples($samples, $brandPath)
    {
        if (!$samples) {
            return;
        }

        foreach ($samples as $link) {
            if ($sampleFile = $link->getSampleFile()) {
                $newSampleFile = $this->getNewBrandFilePath(
                    $this->getSample()->getBasePath(),
                    $brandPath,
                    $sampleFile
                );
                $link->setSampleFile($newSampleFile);
            }
        }
    }

    /**
     * Get brand file path
     *
     * @param string $basePath
     * @param string $brandPath
     * @param string $file
     * @return mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function getNewBrandFilePath($basePath, $brandPath, $file)
    {
        // If be in brand path ren skip
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        if (dirname($file) === $brandPath) {
            return $file;
        }

        return $this->moveToBrandDir($basePath, $brandPath, $file);
    }

    /**
     * Move file to brand directory
     *
     * @param string $basePath
     * @param string $brandPath
     * @param string $file
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function moveToBrandDir($basePath, $brandPath, $file)
    {
        if (strrpos($file, '.tmp') == strlen($file) - 4) {
            $file = substr($file, 0, strlen($file) - 4);
        }

        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $pathInfo = pathinfo($file);

        if (!isset($pathInfo['basename'])) {
            throw new \Magento\Framework\Exception\FileSystemException(__("File not exist!"));
        }

        // Get brand path destination
        $destFile = $brandPath . '/' . \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
            $this->file->getFilePath(
                $this->mediaDirectory->getAbsolutePath($basePath . $brandPath),
                $pathInfo['basename']
            )
        );

        // move file from default to brand path
        $this->mediaDirectory->renameFile(
            $this->file->getFilePath($basePath, $file),
            $this->file->getFilePath($basePath, $destFile)
        );

        return str_replace('\\', '/', $destFile);
    }
}
