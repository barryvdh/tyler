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
 * @package    Bss_BrandSalesReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandSalesReport\Block\Adminhtml\Brand;

use Magento\Backend\Block\Widget\Grid\Container;

/**
 * Class Report
 * Bss\BrandSalesReport\Block\Adminhtml\Sales
 */
class Report extends Container
{
    protected $_template = 'Magento_Reports::report/grid/container.phtml';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Bss_BrandSalesReport';
        $this->_controller = 'adminhtml_brand_report';
        $this->_headerText = __('Brand Sales Rep Report');
        parent::_construct();

        $this->buttonList->remove('add');
        $this->addButton(
            'filter_form_submit',
            ['label' => __('Show Report'), 'onclick' => 'filterFormSubmit()', 'class' => 'primary']
        );
    }

    /**
     * Get filter URL
     *
     * @return string
     */
    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/report', ['_current' => true]);
    }
}
