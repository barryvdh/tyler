<?php
declare(strict_types=1);

namespace Bss\BrandRepresentative\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Get brand representative email data recursive
 */
class BrandRepresentativeEmailDataRecursiveResolver
{
    const IS_CUSTOMIZE_PER_BRAND_CONFIG = 0;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * BrandRepresentativeEmailDataRecursiveResolver constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param Json $json
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Json $json,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->json = $json;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get brand representative email data recursive
     *
     * @param int $categoryId
     * @param int $level
     * @return array|false
     */
    public function execute($categoryId, $level)
    {
        try {
            /** @var \Magento\Catalog\Model\Category $category */
            $category = $this->categoryRepository->get($categoryId);
            $brandRepresentativeRawData = $category->getData('bss_brand_representative_email');

            if ($brandRepresentativeRawData) {
                $brandRepresentativeEmailData = $this->json->unserialize($brandRepresentativeRawData);

                if (isset($brandRepresentativeEmailData['use_company_configuration']) &&
                    $brandRepresentativeEmailData['use_company_configuration'] == self::IS_CUSTOMIZE_PER_BRAND_CONFIG
                ) {
                    unset($brandRepresentativeEmailData['use_company_configuration']);
                    return [
                        'bss_brand_representative_email' => $brandRepresentativeEmailData,
                        'level' => $level
                    ];
                }
            }

            if (!$category->getParentId()) {
                return false;
            }

            return $this->execute($category->getParentId(), ++$level);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
