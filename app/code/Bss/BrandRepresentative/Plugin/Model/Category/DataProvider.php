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
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Class DataProvider
 *
 * Bss\BrandRepresentative\Plugin\Model\Category
 */
class DataProvider
{
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
     * DataProvider constructor.
     * @param ModuleManager $moduleManager
     * @param RequestInterface $request
     * @param Json $Json
     */
    public function __construct(
        ModuleManager $moduleManager,
        RequestInterface $request,
        Json $Json
    ) {
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->json = $Json;
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
            if (isset($categoryData['bss_brand_representative_email'])) {
                $dataToSave = $this->json->unserialize($categoryData['bss_brand_representative_email']);
                $categoryData['bss_brand_representative_email'] = $dataToSave;
            }
            $newData[$id] = $categoryData;
        }
        return $newData;
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
