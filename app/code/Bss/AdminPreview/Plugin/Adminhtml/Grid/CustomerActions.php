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

namespace Bss\AdminPreview\Plugin\Adminhtml\Grid;

class CustomerActions
{

    /**
     * @var \Magento\Framework\View\Element\UiComponent\ContextInterface
     */
    protected $context;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * CustomerActions constructor.
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\AuthorizationInterface $authorization
    )
    {
        $this->context = $context;
        $this->urlBuilder = $urlBuilder;
        $this->_dataHelper = $dataHelper;
        $this->_authorization = $authorization;
    }

    /**
     * @param \Magento\Customer\Ui\Component\Listing\Column\Actions $subject
     * @param array $dataSource
     * @return array
     */
    public function afterPrepareDataSource(
        \Magento\Customer\Ui\Component\Listing\Column\Actions $subject,
        array $dataSource
    )
    {
        if (isset($dataSource['data']['items'])) {
            if ($this->_dataHelper->isEnable() && $this->_dataHelper->getCustomerGridLoginColumn() == 'actions'
                && $this->_authorization->isAllowed('Bss_AdminPreview::login_button')) {
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')] = $this->prepareItem($item, 'preview');
                }
            } else {
                foreach ($dataSource['data']['items'] as &$item) {
                    $item[$subject->getData('name')] = $this->prepareItem($item);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem($item, $type = null)
    {
        if ($type == 'preview') {
            $urlLogin = $this->urlBuilder->getUrl('adminpreview/customer/login', ['customer_id' => $item['entity_id']]);
            $urlEdit = $this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['entity_id']]);
            $html = '';
            $html .= '<ul style="list-style:none"><li>' .
                '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="' . $urlEdit . '">' . 'Edit' . '</a></li>';
            $html .= '<li><a onMouseOver="this.style.cursor=&#039;pointer&#039;" onclick="window.open(&quot;' . $urlLogin . '&quot;)">' . 'Login' . '</a></li>';
            $html .= '</ul>';
            return $html;
        } else {
            $urlEdit = $this->urlBuilder->getUrl('customer/index/edit', ['id' => $item['entity_id']]);
            return '<a onMouseOver="this.style.cursor=&#039;pointer&#039;" href="' . $urlEdit . '">' . 'Edit' . '</a></li>';
        }

    }
}