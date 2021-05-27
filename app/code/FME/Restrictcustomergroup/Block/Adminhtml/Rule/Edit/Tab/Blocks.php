<?php
/**
 * Class for Restrictcustomergroup Blocks
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Adminhtml\Rule\Edit\Tab;

class Blocks extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_cmsBlock;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Cms\Model\BlockFactory $cmsBlockFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Cms\Model\BlockFactory $cmsBlockFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
      $this->_cmsBlock = $cmsBlockFactory;
      $this->_coreRegistry = $coreRegistry;
      parent::__construct($context, $backendHelper, $data);
    }

    public function _construct()
    {
      parent::_construct();
      $this->setId('restrictcustomergroup_blocks_grid');
      $this->setDefaultSort('block_id');
      $this->setUseAjax(true);
      if ($this->_getBlock() && $this->_getBlock()->getId()) {
          $this->setDefaultFilter(['in_blocks' => 1]);
      }
      if ($this->isReadonly()) {
          $this->setFilterVisibility(false);
      }
    }

    protected function _addColumnFilterToCollection($column)
    {
      if ($column->getId() == 'in_blocks') {
          $blockIds = $this->_getSelectedBlocks();
          if (empty($blockIds)) {
              $blockIds = 0;
          }

          if ($column->getFilter()->getValue()) {
              $this->getCollection()->addFieldToFilter('block_id', ['in' => $blockIds]);
          } else {
              if ($blockIds) {
                  $this->getCollection()->addFieldToFilter('block_id', ['nin' => $blockIds]);
              }
          }
      }
      else {
          parent::_addColumnFilterToCollection($column);
      }
      return $this;
    }

    /**
     * Prepare collection
     *
     * @return Extended
     */
    protected function _prepareCollection()
    {
      $collection = $this->_cmsBlock->create()
              ->getCollection();

      if ($this->isReadonly()) {
          $blockIds = $this->_getSelectedBlocks();
          if (empty($blockIds)) {
              $blockIds = [0];
          }

          $collection->addFieldToFilter('block_id', ['in' => $blockIds]);
      }

      $this->setCollection($collection);
      return parent::_prepareCollection();
    }

    protected function _getBlock()
    {
      return $this->_coreRegistry->registry('current_restrictcustomergroup_block');
    }

    public function isReadonly()
    {
      return 0;
    }

    protected function _prepareColumns()
    {
      if (!$this->isReadonly()) {
          $this->addColumn(
              'in_blocks',
              [
              'type' => 'checkbox',
              'name' => 'in_blocks',
              'values' => $this->_getSelectedBlocks(),
              'align' => 'center',
              'index' => 'block_id',
              'header_css_class' => 'col-select',
              'column_css_class' => 'col-select'
                  ]
          );
      }
      $this->addColumn(
          'block_id',
          [
          'header' => __('ID'),
          'sortable' => true,
          'index' => 'block_id',
          'header_css_class' => 'col-id',
          'column_css_class' => 'col-id'
              ]
      );
      $this->addColumn(
          'title_block',
          [
          'header' => __('Title'),
          'index' => 'title',
          'header_css_class' => 'col-name',
          'column_css_class' => 'col-name'
          ]
      );
      $this->addColumn(
          'identifier',
          [
          'header' => __('Identifier'),
          'index' => 'identifier',
          'header_css_class' => 'col-name',
          'column_css_class' => 'col-name'
              ]
      );
      return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
      return $this->getData('grid_url') ?
              $this->getData('grid_url') :
          $this->getUrl('*/*/blocksGrid', ['_current' => true]);
    }

    public function _getSelectedBlocks()
    {
      $blocks = $this->getRelatedBlocks();
      if (!is_array($blocks)) {
          $blocks = array_keys($this->getRuleBlocks());
      }
      return $blocks;
    }

    public function getRuleBlocks()
    {
      $id = $this->getRequest()->getParam('rule_id');
      $blocksArr = [];
      foreach ($this->_coreRegistry->registry('current_restrictcustomergroup_blocks')->getRelatedBlocks($id) as $blocks) {
          $blocksArr[$blocks['block_id']] = ['position' => '0'];
      }
      return $blocksArr;
    }
}
