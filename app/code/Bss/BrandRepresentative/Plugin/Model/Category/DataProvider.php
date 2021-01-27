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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\BrandRepresentative\Plugin\Model\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Class DataProvider
 *
 * Bss\BrandRepresentative\Plugin\Model\Category
 */
class DataProvider
{
    const IS_USE_COMPANY_CATEGORY_CONFIG = 1;
    const IS_CUSTOMIZE_PER_BRAND_CONFIG = 0;

    /**
     * @var array
     * @since 101.0.0
     */
    protected $meta = [];

    /**
     * @var ModuleManager
     * @since 101.0.0
     */
    protected $moduleManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * DataProvider constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ModuleManager $moduleManager
     * @param RequestInterface $request
     * @param Json $Json
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ModuleManager $moduleManager,
        RequestInterface $request,
        Json $Json,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->logger = $logger;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->json = $Json;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Add Meta to Category Form
     *
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $meta
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareMeta(
        \Magento\Catalog\Model\Category\DataProvider $subject,
        array $meta
    ) {
        return $meta;
    }

    public function prepareBssData($data)
    {
        $newData = [];
        foreach ($data as $id => $categoryData) {
            $startLevel = 1;
            $brandRepresentativeEmailData = $this->getBrandRepresentativeEmailDataRecursive($categoryData['entity_id'], $startLevel);
            $categoryData['use_company_configuration'] = self::IS_USE_COMPANY_CATEGORY_CONFIG;

            if ($brandRepresentativeEmailData) {
                if ($brandRepresentativeEmailData['bss_brand_representative_email']) {
                    $categoryData['bss_brand_representative_email'] = $brandRepresentativeEmailData['bss_brand_representative_email'];
                }

                if ($brandRepresentativeEmailData['level'] == 1) {
                    $categoryData['use_company_configuration'] = self::IS_CUSTOMIZE_PER_BRAND_CONFIG;
                }
            }

            $newData[$id] = $categoryData;
        }

        return $newData;
    }

    /**
     * Get brand representative email data recursive
     *
     * @param int $categoryId
     * @param int $level
     * @return array|false
     */
    private function getBrandRepresentativeEmailDataRecursive($categoryId, $level)
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

            return $this->getBrandRepresentativeEmailDataRecursive($category->getParentId(), ++$level);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, array $result)
    {
        return $this->prepareBssData($result);
    }
}
