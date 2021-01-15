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

namespace Bss\BrandRepresentative\Plugin\Controller\Adminhtml\Category;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Save
 * Bss\BrandRepresentative\Plugin\Controller\Adminhtml\Category
 */
class Save
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirect;

    /**
     * Save constructor.
     * @param RequestInterface $request
     * @param CategoryRepository $categoryRepository
     * @param Json $json
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirect
     */
    public function __construct(
        RequestInterface $request,
        CategoryRepository $categoryRepository,
        Json $json,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirect
    ) {
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        $this->json = $json;
        $this->messageManager = $messageManager;
        $this->resultRedirect = $resultRedirect;
    }

    /**
     * Plugin to save additional data to Category
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Category\Save $subject
     * @param ResultInterface $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Redirect
     */
    public function afterExecute($subject, ResultInterface $result)
    {
        $params = $this->request->getParams();
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirect->create();
        $path = 'catalog/*/edit';
        $paramRedirect['id'] = $params['entity_id'];
        $dataRedirect = ['path' => $path, 'params' => $paramRedirect];

        try {
            /* @var Category $category */
            $category = $this->categoryRepository->get($params['entity_id'], $params['store_id']);
            $saveData = '';
            if (isset($params['bss_brand_representative_email'])) {
                $saveData = $this->json->serialize($params["bss_brand_representative_email"]);
            }

            $category->setData('bss_brand_representative_email', $saveData);
            $category->save();

            return $resultRedirect->setPath(
                $dataRedirect['path'],
                $dataRedirect['params']
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not save Category!'));
        }
    }
}
