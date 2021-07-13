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
 * @package    Bss_InventoryReport
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\InventoryReport\Controller\Adminhtml\Report;

use Bss\InventoryReport\Block\Adminhtml\Inventory\Report\Grid;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class ExportMyCustomReportCsv
 * Bss\InventoryReport\Controller\Adminhtml\Report\Sales
 */
class ExportCsv extends Sales
{
    /**
     * Do export csv
     *
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $time = time();
        $fileName = "inventory_report_$time.csv";
        $grid = $this->_view->getLayout()
            ->createBlock(Grid::class);
        $this->_initReportAction($grid);

        return $this->_fileFactory->create($fileName, $grid->getCsvFile(), DirectoryList::VAR_DIR);
    }
}
