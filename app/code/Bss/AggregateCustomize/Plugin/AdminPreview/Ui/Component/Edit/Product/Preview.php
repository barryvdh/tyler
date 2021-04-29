<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\AdminPreview\Ui\Component\Edit\Product;

use Bss\AdminPreview\Plugin\FrontendUrl;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Preview
 * Fix preview product url
 */
class Preview
{
    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Preview constructor.
     *
     * @param FrontendUrl $frontendUrl
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        FrontendUrl $frontendUrl,
        StoreManagerInterface $storeManager
    ) {
        $this->frontendUrl = $frontendUrl;
        $this->storeManager = $storeManager;
    }

    /**
     * Fix preview url
     *
     * @param \Bss\AdminPreview\Ui\Component\Edit\Product\Preview $subject
     * @param string $productUrl
     * @param int $productId
     * @param int|null $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductUrl(
        $subject,
        $productUrl,
        $productId,
        $storeId
    ) {
        if ($storeId) {
            $store = $this->storeManager->getStore($storeId);
        } else {
            $store = $this->storeManager->getDefaultStoreView();
        }

        return $this->frontendUrl->getFrontendUrl()
            ->setScope($store)
            ->getUrl("adminpreview/preview/index", [
                "product_id" => $productId
            ]);
    }
}
