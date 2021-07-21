<?php
declare(strict_types=1);
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
 * @package    Bss_LatestProductListing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\LatestProductListing\Setup;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Cms\Api\Data\PageInterfaceFactory as PageFactory;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Create latest produc listing cms page
 */
class InstallData implements InstallDataInterface
{
    const PAGE_IDENTIFIER = "new.html";

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * InstallData constructor.
     *
     * @param PageFactory $pageFactory
     * @param PageRepositoryInterface $pageRepository
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        PageFactory $pageFactory,
        PageRepositoryInterface $pageRepository,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Create latest produc listing cms page
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        try {
            $defaultStore = $this->storeManager->getDefaultStoreView();
            /** @var CmsPage $page */
            $page = $this->pageFactory->create();
            $page->setIdentifier(self::PAGE_IDENTIFIER)
                ->setIsActive(true)
                ->setTitle("Newest Products")
                ->setPageLayout('full-width')
                ->setContent(
                    '{{block class="Bss\LatestProductListing\Block\Product\ListNew" name="product-listing"' .
                    ' template="Bss_LatestProductListing::product/list-new.phtml"}}'
                )
                ->setStores([$defaultStore->getId()]);

            $this->pageRepository->save($page);
        } catch (\Exception $e) {
            $this->logger->error(
                __("BSS.ERROR: Create CMS Page Failed. %1", $e)
            );
        }
    }
}
