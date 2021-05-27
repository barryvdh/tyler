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

namespace Bss\Popup\Controller\Render;

class Time extends \Magento\Framework\App\Action\Action
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
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;

    /**
     * Time constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetime
     * @param \Bss\Popup\Helper\Data $helper
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetime,
        \Bss\Popup\Helper\Data $helper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\App\Action\Context $context

    ) {
        $this->datetime = $datetime;
        $this->popupFactory = $popupFactory;
        $this->helper = $helper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $popupId = $this->getRequest()->getPost('popupId');
            $result['res'] = 'false';

            if ($popupId) {
                $popup = $this->popupFactory->create()->load($popupId);

                if ($this->isResultRes($popup)) {
                    $result['res'] = true;
                }
            } else {
                $result['res'] = false;
            }

            /** @var \Magento\Framework\Controller\Result\Json $response */
            $response = $this->resultJsonFactory->create()->setData($result);
            return $response;

        } else {
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('no-route');
        }
    }

    /**
     * @param $popup
     * @return bool
     */
    protected function isResultRes($popup)
    {
        $date = $this->datetime->date()->format('Y-m-d H:i:s');
        $startTime = $popup->getData('display_from');
        $endTime = $popup->getData('display_to');
        // If it is preview mode
        // dont check time to show
        $isPreview = $this->getRequest()->getPost('isPreview');
        $isPreview = $isPreview ?: $this->getRequest()->getPost('preview');
        if ($isPreview) {
            return true;
        }
        // End
        if ($this->helper->popupIsAllowedDisplay($popup->getData())) {
            if ((($startTime) !== null && $startTime <= $date) ||
                (($endTime) !== null && $endTime >= $date)
            ) {
                return true;
            } elseif ($startTime === null || $endTime === null) {
                return true;
            }
        }
        return false;
    }
}