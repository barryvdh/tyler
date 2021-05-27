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

namespace Bss\Popup\Controller\Adminhtml;

abstract class Popup extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\Popup\Helper\Layout
     */
    protected $layoutHelper;

    /**
     * Popup Factory
     *
     * @var \Bss\Popup\Model\PopupFactory
     */
    protected $popupFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * Constructor
     *
     * @param \Bss\Popup\Model\PopupFactory $popupFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Bss\Popup\Helper\Layout $layoutHelper,
        \Bss\Popup\Model\PopupFactory $popupFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->layoutHelper = $layoutHelper;
        $this->popupFactory = $popupFactory;
        $this->coreRegistry = $coreRegistry;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * Init Popup
     *
     * @return \Bss\Popup\Model\Popup
     */
    protected function _initPopup()
    {
        $popupParams = (int)$this->getRequest()->getParam('popup');
        /** @var \Bss\Popup\Model\Popup $Popup */
        $popup = $this->popupFactory->create();
        if (isset($popupParams['popup_id'])) {
            $popup->load($popupParams['popup_id']);
        }
        $this->coreRegistry->register('bss_popup_popup', $popup);
        return $popup;
    }
}
