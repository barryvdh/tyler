<?php
declare(strict_types=1);
namespace Bss\DigitalAssetsManage\Plugin;

use Bss\DigitalAssetsManage\Helper\UniqueFileName;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Framework\Registry;

/**
 * Class MoveImage
 */
class MoveImage
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var UniqueFileName
     */
    protected $uniqueFileName;

    /**
     * MoveImage constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param Registry $registry
     * @param UniqueFileName $uniqueFileName
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Registry $registry,
        UniqueFileName $uniqueFileName
    ) {
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->uniqueFileName = $uniqueFileName;
    }

    public function afterExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\Save $subject,
        $result
    ) {
        $product = $this->registry->registry('current_product');

        // $this->uniqueFileName->moveProductImagesToBrandDir($product);

        return $result;
    }
}
