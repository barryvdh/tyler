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

use Magento\Framework\App\RequestInterface;
use Bss\BrandRepresentative\Helper\BrandRepresentativeEmailDataRecursiveResolver;

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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var BrandRepresentativeEmailDataRecursiveResolver
     */
    private $brandRepresentativeEmailDataResolver;

    /**
     * DataProvider constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param RequestInterface $request
     * @param BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        RequestInterface $request,
        BrandRepresentativeEmailDataRecursiveResolver $brandRepresentativeEmailDataResolver
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->brandRepresentativeEmailDataResolver = $brandRepresentativeEmailDataResolver;
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

    /**
     * Prepare brand representative email data
     *
     * @param array $data
     * @return array
     */
    public function prepareBssData($data)
    {
        $newData = [];
        foreach ($data as $id => $categoryData) {
            $startLevel = 1;
            $categoryData['use_company_configuration'] = self::IS_USE_COMPANY_CATEGORY_CONFIG;
            $categoryData['bss_brand_representative_email'] = [];
            $requestId = $categoryData['entity_id'] ?? $this->request->getParam('parent');

            // Is add category action
            if (!isset($categoryData['entity_id'])) {
                if ($this->request->getParam('parent')) {
                    $startLevel = 2;
                }
            }

            if (!$requestId) {
                continue;
            }

            $brandRepresentativeEmailData = $this->brandRepresentativeEmailDataResolver
                ->execute($requestId, $startLevel);

            if ($brandRepresentativeEmailData) {
                if ($brandRepresentativeEmailData['bss_brand_representative_email']) {
                    $categoryData['bss_brand_representative_email'] =
                        $brandRepresentativeEmailData['bss_brand_representative_email'];
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
     * Add brand representative email data to category form data
     *
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
