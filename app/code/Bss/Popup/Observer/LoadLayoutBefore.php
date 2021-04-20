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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Popup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class LoadLayoutBefore implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Bss\Popup\Model\Form\FormKey
     */
    private $formKey;

    /**
     * LoadLayoutBefore constructor.
     * @param RequestInterface $request
     * @param \Bss\Popup\Model\Form\FormKey $formKey
     */
    public function __construct(
        RequestInterface $request,
        \Bss\Popup\Model\Form\FormKey $formKey
    ) {
        $this->_request = $request;
        $this->formKey = $formKey;
    }

    /**
     * @inheritDoc
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $observer->getData('layout');
        $fullActionName = $observer->getData('full_action_name');
        $isPreview = $this->getRequest()->getParam('preview');
        $popupId = $this->getRequest()->getParam('id');
        $key = $this->getRequest()->getParam('cid');
        $formKey = $this->formKey->getFormKey();
        if ($fullActionName == 'cms_index_index' &&
            $isPreview && $key == $formKey) {
            // If we are in home page
            // and it is preview mode
            // and popup id is valid
            // then unset default layout and set new data to block to preview
            $popup = $layout->getBlock('popup_default');
            $params = $this->getRequest()->getParam('popup');
            $params = $this->addWeirdParams($params);
            $params['popup_id'] = $popupId;
            $popup->setData('preview', $params);
            $popup->setData('mode', 'display_all');
        }
        return $this;
    }

    /**
     * @param $params
     * @return array
     */
    private function addWeirdParams($params)
    {
        $weirdParams = [
            'priority',
            'display_from',
            'display_to',
            'priority',
            'event_display',
            'after_load',
            'after_scroll',
            'page_view',
            'effect_display',
            'position',
            'hide_after',
            'close_outside',
            'frequently',
            'cookie_expires',
            'exit_intent_appears'
        ];
        foreach ($weirdParams as $paramName) {
            if (!isset($params[$paramName]) || !$params[$paramName]) {
                if ($paramName == 'display_from' ||
                    $paramName == 'display_to' ||
                    $paramName == 'exit_intent_appears') {
                    $params[$paramName] = null;
                } else {
                    $params[$paramName] = 0;
                }
            }
        }
        return $params;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }
}
