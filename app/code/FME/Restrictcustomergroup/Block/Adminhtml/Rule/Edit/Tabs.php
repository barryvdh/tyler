<?php
/**
 * Class for Restrictcustomergroup Tabs
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */

namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit;

use \FME\Restrictcustomergroup\Helper\Data;

/**
 * Admin page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     *
     * @var \FME\Restrictcustomergroup\Helper\Data $_restrictcustomergroupHelper
     */
    protected $_restrictcustomergroupHelper;

    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        Data $_restrictcustomergroupHelper,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_restrictcustomergroupHelper = $_restrictcustomergroupHelper;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
      parent::_construct();
      $this->setId('rule_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(__('Rule Information'));
    }

    protected function _beforeToHtml()
    {
      $model = $this->_coreRegistry->registry('restrictcustomergroup_data');
      $this->addTab(
          'related_blocks',
          [
          'label' => __('Blocks'),
          'url' => $this->getUrl('*/*/blocks', ['_current' => true]),
          'class' => 'ajax',
          ]
      );
      parent::_beforeToHtml();
    }

}
