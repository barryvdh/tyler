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
 * @copyright  Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Popup\Controller\Adminhtml\Popup;

class Save extends \Bss\Popup\Controller\Adminhtml\Popup
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Bss\Popup\Helper\Layout
     */
    protected $layoutHelper;

    /**
     * @var \Bss\Popup\Model\Form\FormKey
     */
    private $formKey;

    /**
     * Save constructor.
     * @param \Bss\Popup\Helper\Layout $layoutHelper
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Bss\Popup\Model\Form\FormKey $formKey
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Bss\Popup\Helper\Layout $layoutHelper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context,
        \Bss\Popup\Model\Form\FormKey $formKey
    ) {
        $this->typeList=$typeList;
        $this->backendSession = $context->getSession();
        $this->layoutHelper = $layoutHelper;
        $this->formKey = $formKey;
        parent::__construct($layoutHelper, $popupFactory, $registry, $context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $layoutData = $this->getRequest()->getPost('widget_instance');
        $data = $this->getRequest()->getPost('popup');
        $data = $this->layoutHelper->filterPostData($data);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $popup = $this->_initPopup();
            $fromDate = $data['display_from'];
            $toDate = $data['display_to'];

            if (!$this->layoutHelper->validateRangeDate($fromDate, $toDate)) {
                $this->messageManager->addErrorMessage(__('You must set the Start Date sooner than the End Date.'));
                $this->_getSession()->setBssPopupPopupData($data);
                $resultRedirect->setPath(
                    'bss_popup/*/edit',
                    [
                        'popup_id' => $popup->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
            if (isset($data["type_template"]) && $data["type_template"] ==
                \Bss\Popup\Model\Source\Template::TEMPLATE_AGE_VERIFICATION) {
                $data["hide_after"] = 0;
            }
            $popup->setData($data);

            try {
                $popup->save();
                if (isset($data['popup_id'])) {
                    $popupId = $data['popup_id'];
                    $this->layoutHelper->deleteOldLayoutUpadte($popupId);
                } else {
                    $popupId = $popup->getId();
                }
                if (!empty($layoutData)) {
                    $dataFormat = $this->layoutHelper->formatData($layoutData, $popupId);
                    $this->layoutHelper->deleteOldLayout($popupId, $dataFormat['isset']);
                    foreach ($dataFormat['data'] as $dt) {
                        $this->layoutHelper->saveLayout($dt, $popupId);
                    }
                } else {
                    $this->layoutHelper->deleteOldLayout($popupId, []);
                }
                $this->messageManager->addSuccessMessage(__('The Pop-up has been saved.'));
                $this->typeList->invalidate(
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                );
                $this->backendSession->setBssPopupPopupData(false);

                // Clear form key
                $this->formKey->renewData();
                // End
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'bss_popup/*/edit',
                        [
                            'popup_id' => $popup->getId(),
                            '_current' => true
                        ]
                    );
                    return $resultRedirect;
                }

                $resultRedirect->setPath('bss_popup/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }

            $this->_getSession()->setBssPopupPopupData($data);
            $resultRedirect->setPath(
                'bss_popup/*/edit',
                [
                    'popup_id' => $popup->getId(),
                    '_current' => true
                ]
            );
            return $resultRedirect;
        }

        $resultRedirect->setPath('bss_popup/*/');
        return $resultRedirect;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_Popup::save");
    }
}
