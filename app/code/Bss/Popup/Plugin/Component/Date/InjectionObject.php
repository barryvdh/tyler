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
namespace Bss\Popup\Plugin\Component\Date;

use Bss\Popup\Component\Listing\Columns\Date;
use Bss\Popup\Helper\Data as PopupHelper;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class InjectionObject
 *
 * @package Bss\Popup\Plugin\Component\Date
 */
class InjectionObject
{
    /**
     * @var BooleanUtils
     */
    protected $booleanUtils;

    /**
     * @var PopupHelper
     */
    protected $popupHelper;

    /**
     * InjectionObject constructor.
     *
     * @param BooleanUtils $booleanUtils
     * @param PopupHelper $popupHelper
     */
    public function __construct(
        BooleanUtils $booleanUtils,
        PopupHelper $popupHelper
    ) {
        $this->booleanUtils = $booleanUtils;
        $this->popupHelper = $popupHelper;
    }

    /**
     * @param Date $dateObj
     * @param $result
     * @return BooleanUtils
     */
    public function afterGetBooleanUtilsObj(
        Date $dateObj,
        $result
    ) {
        if (!$result || !($result instanceof BooleanUtils)) {
            return $this->booleanUtils;
        }
        return $result;
    }

    /**
     * @param Date $dateObj
     * @param $result
     * @return popupHelper
     */
    public function afterGetPopupHelper(
        Date $dateObj,
        $result
    ) {
        if (!$result || !($result instanceof PopupHelper)) {
            return $this->popupHelper;
        }
        return $result;
    }
}