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
namespace Bss\Popup\Component\Listing\Columns;

use Bss\Popup\Helper\Data as PopupHelper;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class Date
 *
 * @package Bss\Popup\Component\Listing\Columns
 */
class Date extends \Magento\Ui\Component\Listing\Columns\Date
{
    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');

        // For M2.4, we must work on timezone = false
        // In case, timezone set to false, and is M2.4 version
        $popupHelper = $this->getPopupHelper();
        if ($popupHelper->isM24Version() &&
            isset($config['timezone']) &&
            !$config['timezone']) {
            unset($config['timezone']);
            $config['bss_timezone'] = true;
        }
        // End custom

        $this->setData('config', $config);

        parent::prepare();
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== "0000-00-00 00:00:00"
                ) {
                    $date = $this->timezone->date(new \DateTime($item[$this->getData('name')]));
                    $timezone = $this->getTimezone();
                    if (!$timezone) {
                        $date = new \DateTime($item[$this->getData('name')]);
                    }
                    $item[$this->getData('name')] = $date->format('Y-m-d H:i:s');
                }
            }
        }

        return $dataSource;
    }

    /**
     * @return bool|mixed
     */
    protected function getTimezone()
    {
        if (isset($this->getConfiguration()['bss_timezone']) &&
            $this->getConfiguration()['bss_timezone']) {
            return false;
        } elseif (isset($this->getConfiguration()['timezone'])) {
            $booleanUtilsObj = $this->getBooleanUtilsObj();
            return $booleanUtilsObj->convert($this->getConfiguration()['timezone']);
        } else {
            return true;
        }
    }

    /**
     * @return BooleanUtils|null
     */
    public function getBooleanUtilsObj()
    {
        return null;
    }

    /**
     * @return PopupHelper|null
     */
    public function getPopupHelper()
    {
        return null;
    }
}
