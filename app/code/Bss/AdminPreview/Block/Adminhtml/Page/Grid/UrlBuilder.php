<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Block\Adminhtml\Page\Grid;

/**
 * Url builder class used to compose dynamic urls.
 */
class UrlBuilder extends \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $frontendUrlBuilder;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @param \Magento\Framework\UrlInterface $frontendUrlBuilder
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\UrlInterface $frontendUrlBuilder,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    )
    {
        parent::__construct($frontendUrlBuilder);
        $this->productMetadata = $productMetadata;
    }

    /**
     * Get action url
     * Reference: https://github.com/magento/magento2/commit/d6f7d244fffade4764afb53fc0d4b932f5b180a1
     * @param string $routePath
     * @param string $scope
     * @param string $store
     * @return string
     */
    public function getUrl($routePath, $scope, $store)
    {
        if ($this->productMetadata->getVersion() == '2.3.0') {
            if ($scope) {
                $this->frontendUrlBuilder->setScope($scope);
                $href = $this->frontendUrlBuilder->getUrl(
                    $routePath,
                    [
                        '_current' => false,
                        '_nosid' => true,
                        '_query' => [\Magento\Store\Model\StoreManagerInterface::PARAM_NAME => $store]
                    ]
                );
            } else {
                $href = $this->frontendUrlBuilder->getUrl(
                    $routePath,
                    [
                        '_current' => false,
                        '_nosid' => true
                    ]
                );
            }
            return $href;
        }

        return parent::getUrl($routePath, $scope, $store);
    }
}
