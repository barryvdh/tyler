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

namespace Bss\BrandSalesReport\Controller\Adminhtml\Report\BrandSales;

use Bss\BrandSalesReport\Model\Flag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class Report
 * Bss\BrandSalesReport\Controller\Adminhtml\Report\Sales
 */
class Report extends Sales
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_BRANDSALESREPORT_FLAG_CODE, 'brandsales_report');

        $this->_initAction()->_setActiveMenu(
            'Bss_BrandSalesReport::report_brand_report'
        )->_addBreadcrumb(
            __('Brand Sales Report'),
            __('Brand Sales Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Brand Sales Report'));

        $gridBlock = $this->_view->getLayout()
            ->getBlock('adminhtml_brand_report.grid');

        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction([$gridBlock, $filterFormBlock]);

        $this->_view->renderLayout();
    }
}
