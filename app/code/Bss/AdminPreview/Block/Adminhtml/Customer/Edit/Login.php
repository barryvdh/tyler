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
namespace Bss\AdminPreview\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Login as customer button
 */
class Login extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $customerId = $this->getCustomerId();
        $data = [];
        $canModify = $customerId && $this->_authorization->isAllowed('Bss_AdminPreview::login_button');
        if ($canModify) {
            $data = [
                'label' => __('Login As Customer'),
                'class' => 'login login-button',
                'on_click' => 'window.open( \'' . $this->getInvalidateTokenUrl() .
                    '\')',
                'sort_order' => 70,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getInvalidateTokenUrl()
    {
        return $this->getUrl('adminpreview/customer/login', ['customer_id' => $this->getCustomerId()]);
    }

}
