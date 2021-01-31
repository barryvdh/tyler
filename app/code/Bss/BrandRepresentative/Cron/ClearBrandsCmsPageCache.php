<?php
declare(strict_types=1);
namespace Bss\BrandRepresentative\Cron;

use Magento\Cms\Api\PageRepositoryInterface;

/**
 * Class ClearBrandsCmsPageCache
 */
class ClearBrandsCmsPageCache
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $fullPageCache;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * ClearBrandsCmsPageCache constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\PageCache\Model\Cache\Type $fullPageCache
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PageRepositoryInterface $pageRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\PageCache\Model\Cache\Type $fullPageCache,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        PageRepositoryInterface $pageRepository
    ) {
        $this->logger = $logger;
        $this->fullPageCache = $fullPageCache;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Clear the brands cms cache page
     */
    public function execute()
    {
        try {
            $this->searchCriteriaBuilder->addFilter('identifier', 'brands');
            $pages = $this->pageRepository->getList($this->searchCriteriaBuilder->create());
            foreach ($pages->getItems() as $page) {
                if ($page) {
                    $cacheTag = $page->getId();
                }
            }

            if (isset($cacheTag)) {
                $cacheTag = "cms_p_" . $cacheTag;
                $this->fullPageCache->clean(
                    \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                    [$cacheTag]
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
