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
namespace Bss\BrandRepresentative\Observer;

use Exception;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategorySaveAfter
 * Bss\BrandRepresentative\Observer
 */
class CategorySaveAfter implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CategoryFactory
     */
    protected $category;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * CategorySaveAfter constructor.
     * @param RequestInterface $request
     * @param CategoryFactory $category
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        RequestInterface $request,
        CategoryFactory $category,
        Json $json,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        CategoryRepository $categoryRepository
    ) {
        $this->request = $request;
        $this->category = $category;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer)
    {
        $params = $this->request->getParams();
        if (isset($params['bss_brand_representative_email'])) {
            $dataSave = $this->json->serialize($params['bss_brand_representative_email']);
            try {
                $currentCat= $observer->getData('category');
                if ($currentCat) {
                    $currentCatId = $currentCat->getId();
                    $category = $this->categoryRepository
                        ->get($currentCatId, $this->storeManager->getStore()->getId());
                    $category->setStoreId($this->storeManager->getStore()->getId());
                    $category->setData('bss_brand_representative_email', $dataSave);
                    $category->save();
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Could not save this category!'));
            } catch (NoSuchEntityException $exception) {

            }
        }

        // TODO: Implement execute() method.
    }
}
