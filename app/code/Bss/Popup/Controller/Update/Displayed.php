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

namespace Bss\Popup\Controller\Update;

class Displayed extends \Magento\Framework\App\Action\Action
{
    /**
     * Result Json Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Popup Factory
     *
     * @var \Bss\Popup\Model\PopupFactory
     */
    protected $popupFactory;

    /**
     * Helper
     *
     * @var \Bss\Popup\Helper\Data
     */
    protected $helper;

    /**
     * Displayed constructor.
     * @param \Bss\Popup\Helper\Data $helper
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Bss\Popup\Helper\Data $helper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\App\Action\Context $context

    ) {
        $this->popupFactory = $popupFactory;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $popupId = $this->getRequest()->getPost('popupId');
            $popupModel = $this->popupFactory->create()->load($popupId);

            if ($popupModel) {
                $popup = $popupModel->getData();
                switch ($popup['frequently']) {
                    case 2:
                        $this->helper->addPopupToSessionDisplayedPopup($popupId);
                        $result['res'] = "2";
                        break;
                    case 3:
                        $this->helper->addPopupToCookie($popupId, $popup['cookie_expires'] * 60);
                        $result['res'] = "3";
                        break;
                    default:
                        $result['res'] = "default";
                        break;
                }
            } else {
                $result['res'] = "fail";
            }

            /** @var \Magento\Framework\Controller\Result\Json $response */
            $response = $this->resultJsonFactory->create()->setData($result);
            return $response;

        } else {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('no-route');
        }
    }
}
