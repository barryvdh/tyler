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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Controller\Adminhtml\Popup;

class Edit extends \Bss\Popup\Controller\Adminhtml\Popup
{
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\Popup\Helper\Layout $layoutHelper
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\Popup\Helper\Layout $layoutHelper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($layoutHelper, $popupFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('popup_id');
        /** @var \Bss\Popup\Model\Popup $file */
        $popup = $this->_initPopup();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Bss_Popup::Popup')
            ->getConfig()->getTitle()->set(__('Pop-up'));
        if ($id) {
            $popup->load($id);
            if (!$popup->getId()) {
                $this->messageManager->addErrorMessage(__('This Pop-up no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'bss_popup/*/edit',
                    [
                        'popup_id' => $popup->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }

        $title = $popup->getId() ?
            __("%1", $popup->getPopupName()) :
            __('New Pop-up');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->_session->getData('bss_popup_popup_data', true);

        if (!empty($data)) {
            $popup->setData($data);
        }
        return $resultPage;
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_Popup::save");
    }
}