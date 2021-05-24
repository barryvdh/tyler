<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Observer;

use Bss\DigitalAssetsManage\Helper\UniqueFileName;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Observer
 * Move image from category base path to brand path
 */
class MoveCategoryDigitalAssetsObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var UniqueFileName
     */
    protected $uniqueFileName;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * MoveCategoryDigitalAssetsObserver constructor.
     *
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param CategoryRepositoryInterface $categoryRepository
     * @param UniqueFileName $uniqueFileName
     * @param ImageUploader|null $imageUploader
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        CategoryRepositoryInterface $categoryRepository,
        UniqueFileName $uniqueFileName,
        ImageUploader $imageUploader = null
    ) {
        $this->filesystem = $filesystem;
        $this->categoryRepository = $categoryRepository;
        $this->uniqueFileName = $uniqueFileName;
        $this->imageUploader = $imageUploader ??
            ObjectManager::getInstance()->get(ImageUploader::class);
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        return;
        /** @var Category $category */
        $category = $observer->getData('category');
        if (!$category ||
            !preg_match("/digital\sassets/i", $category->getName() . "") ||
            !$category->getImage()
        ) {
            return;
        }

        $brandPath = $this->getBrandPath($category);
        if (!$brandPath) {
            return;
        }

        if (strpos($category->getImage(), $brandPath) !== false) {
//            vadu_log('out');
            return;
        }

        if (!$this->fileResidesOutsideCategoryDir($category->getImage())) {
            if ($brandPath = $this->getBrandPath($category)) {
                // phpcs:disable Magento2.Functions.DiscouragedFunction
                $pathInfo = pathinfo($category->getImage());
                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $basePath = $this->imageUploader->getBasePath();
                $uniqueName = $basePath . DIRECTORY_SEPARATOR . $brandPath . DIRECTORY_SEPARATOR .
                    \Magento\MediaStorage\Model\File\Uploader::getNewFileName(
                        $this->imageUploader->getFilePath(
                            $mediaDirectory->getAbsolutePath($basePath . $brandPath),
                            $pathInfo['basename']
                        )
                    );

                $pattern = str_replace("/", "\/", $basePath);
                preg_match("/" . $pattern . "\/.*/", $category->getImage(), $matchs);
                try {
                    if ($matchs) {
                        $mediaDirectory->renameFile(
                            $matchs[0],
                            $uniqueName
                        );

                        $category->setImage(
                            DIRECTORY_SEPARATOR .
                            $category->getStore()->getBaseMediaDir() .
                            DIRECTORY_SEPARATOR .
                            $uniqueName
                        );
                        $this->categoryRepository->save($category);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    return;
                }
            }
        }
    }

    /**
     * Get brand path
     *
     * @param Category $category
     * @return false|string
     */
    protected function getBrandPath($category)
    {
        $brandPath = $this->uniqueFileName->getBrandName($category);

        if ($brandPath) {
            return DIRECTORY_SEPARATOR . $brandPath . DIRECTORY_SEPARATOR . UniqueFileName::DIGITAL_ASSETS_FOLDER_NAME;
        }

        return false;
    }

    /**
     * Check for file path resides outside of category media dir. The URL will be a path including pub/media if true
     *
     * @param string $path
     * @return bool
     */
    private function fileResidesOutsideCategoryDir($path)
    {
        if (!$path) {
            return false;
        }

        $fileUrl = ltrim($path, '/');
        $baseMediaDir = $this->filesystem->getUri(DirectoryList::MEDIA) .
            DIRECTORY_SEPARATOR .
            $this->imageUploader->getBasePath();

        if (!$baseMediaDir) {
            return false;
        }

        return strpos($fileUrl, $baseMediaDir) === false;
    }
}
