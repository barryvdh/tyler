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

namespace Bss\Popup\Block\Adminhtml\Chooser;

class Layout extends \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Chooser\Layout
{
    /**
     * @return \Magento\Framework\View\Element\AbstractBlock|\Magento\Framework\View\Element\Html\Select
     */
    protected function _beforeToHtml()
    {
        if (!$this->getOptions()) {
            $pageTypes = $this->_config->getPageTypes();
            $this->_addPageTypeOptions($pageTypes);
        }
        return \Magento\Framework\View\Element\Html\Select::_beforeToHtml();
    }
}
