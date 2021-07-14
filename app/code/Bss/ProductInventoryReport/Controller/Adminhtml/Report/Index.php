<?php
declare(strict_types=1);
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
 * @package    Bss_ProductInventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductInventoryReport\Controller\Adminhtml\Report;

use Bss\ProductInventoryReport\Model\Flag;
use Magento\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Report listing page
 */
class Index extends Sales
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_ProductInventoryReport::inventory_report';

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_showLastExecutionTime(Flag::REPORT_INVENTORY_REPORT_FLAG_CODE, 'inventory_report');
        $this->_initAction()->_setActiveMenu(
            'Bss_ProductInventoryReport::inventory_report'
        )->_addBreadcrumb(
            __('Inventory Report'),
            __('Inventory Report')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Inventory Report'));
        $gridBlock = $this->_view->getLayout()
            ->getBlock('adminhtml_inventory_report.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction([$gridBlock, $filterFormBlock]);
        $this->_view->renderLayout();
    }
}
