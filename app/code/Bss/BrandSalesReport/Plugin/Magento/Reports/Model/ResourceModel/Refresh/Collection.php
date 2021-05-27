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

namespace Bss\BrandSalesReport\Plugin\Magento\Reports\Model\ResourceModel\Refresh;

use Bss\BrandSalesReport\Model\Flag;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Reports\Model\FlagFactory;

/**
 * Class Collection
 * Bss\BrandSalesReport\Plugin\Magento\Reports\Model\ResourceModel\Refresh
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var TimezoneInterface
     */
    protected $_localeDate;

    /**
     * @var FlagFactory
     */
    protected $_reportsFlagFactory;

    /**
     * @param EntityFactory $entityFactory
     * @param TimezoneInterface $localeDate
     * @param FlagFactory $reportsFlagFactory
     */
    public function __construct(
        EntityFactory $entityFactory,
        TimezoneInterface $localeDate,
        FlagFactory $reportsFlagFactory
    ) {
        parent::__construct($entityFactory);
        $this->_localeDate = $localeDate;
        $this->_reportsFlagFactory = $reportsFlagFactory;
    }

    /**
     * Get if updated
     *
     * @param string $reportCode
     * @return string
     */
    protected function _getUpdatedAt(string $reportCode)
    {
        $flag = $this->_reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();
        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterLoadData($subject, $result, $printQuery = false, $logQuery = false)
    {
        if (!count($this->_items)) {
            $data = [
                [
                    'id' => 'brandsalesreport',
                    'report' => __('Brand Sales Report'),
                    'comment' => __('Brand Sales Report'),
                    'updated_at' => $this->_getUpdatedAt(Flag::REPORT_BRANDSALESREPORT_FLAG_CODE)
                ],
            ];
            foreach ($data as $value) {
                $item = new DataObject();
                $item->setData($value);
                $this->addItem($item);
                $subject->addItem($item);
            }
        }
        return $subject;
    }
}
