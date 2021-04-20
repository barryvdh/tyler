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
namespace Bss\AdminPreview\Controller\Adminhtml\Edit;

/**
 * Class Redirect
 * @package Bss\AdminPreview\Controller\Adminhtml\Edit
 */
class Redirect extends \Magento\Backend\App\Action
{

    /**
     * @var array
     */
    protected $_publicActions = ['redirect'];

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {   
        $type = $this->getRequest()->getParam('type');
        if ($type == 'customer') {
            $customerId = $this->getRequest()->getParam('id');
            $redirectUrl = $this->_helper->getUrl('customer/index/edit', ['id' => $customerId]);
        } elseif ($type == 'category') {
            $category_id = $this->getRequest()->getParam('category_id');
            $storeId = $this->getRequest()->getParam('storeId');
            $redirectUrl = $this->_helper->getUrl('catalog/category/edit', ['id' => $category_id,'store' => $storeId]);
        } elseif ($type == 'cms_page') {
            $page_id = $this->getRequest()->getParam('page_id');
            $redirectUrl = $this->_helper->getUrl('cms/page/edit', ['page_id' => $page_id]);
        } elseif ($type == 'cms_block') {
            $block_id = $this->getRequest()->getParam('block_id');
            $redirectUrl = $this->_helper->getUrl('cms/block/edit', ['block_id' => $block_id]);
        } else {
            $redirectUrl = $this->_helper->getUrl('admin/dashboard');
        }
        $this->getResponse()->setRedirect($redirectUrl);
    }
}
