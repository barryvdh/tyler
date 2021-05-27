<?php
/**
 * Class for Restrictcustomergroup Block
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Block\Cms\Block\Widget;

use Magento\Customer\Model\Session;

class Block extends \Magento\Cms\Block\Widget\Block
{
    protected $_ruleFactory;
    protected $_restrictcustomergroupHelper;
    protected $date;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \FME\Restrictcustomergroup\Model\RuleFactory $ruleFactory,
        \FME\Restrictcustomergroup\Helper\Data $helper,
        Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->_restrictcustomergroupHelper = $helper;
        $this->_customerSession = $customerSession;
        $this->date = $date;
        $this->storeManager = $storeManager;
        parent::__construct($context, $filterProvider, $blockFactory, $data);
    }

    /**
     * Prepare Content HTML
     *
     * @return string
     */
    protected function _beforeToHtml()
    {
        if (!$this->_restrictcustomergroupHelper->isEnabledInFrontend())
        {
          parent::_beforeToHtml();
        }

        if ($this->_restrictcustomergroupHelper->isWebCrawler($this->getRequest()))
        {
          parent::_beforeToHtml();
        }

        parent::_beforeToHtml();
        $blockId = $this->getData('block_id');
        $blockHash = get_class($this) . $blockId;
        if (isset(self::$_widgetUsageMap[$blockHash]))
        {
          return $this;
        }
        self::$_widgetUsageMap[$blockHash] = true;

        $restrictcustomergroup = $this->_ruleFactory->create();

        $collection = $restrictcustomergroup->getCollection()
                ->addStoreFilter([$this->storeManager->getStore()->getId()], false)
                ->addCustomerGroupFilter($this->_customerSession->getCustomerGroupId())
                ->addStaticBlockFilter($blockId)
                ->addStatusFilter()
                ->addPriorityFilter()
                ->addLimit();

        if ($collection->count() >= 1)
        {
          foreach ($collection as $item)
      		{
            if(empty($item->getData('start_date')) || empty($item->getData('end_date')))
      			{
              $item = $collection->getFirstItem();

              $relatedBlocks = $this->_ruleFactory->create()
                      ->getRelatedBlocks($item->getId());

              $matched = [];
              foreach ($relatedBlocks as $block) {
                  $matched[] = $block['block_id'];
              }

              if ($blockId) {
                  $storeId = $this->_storeManager->getStore()
                          ->getId();
                  /** @var \Magento\Cms\Model\Block $block */
                  $block = $this->_blockFactory->create();
                  $block->setStoreId($storeId)
                          ->load($blockId);

                  if ($block->isActive()) {
                      if (in_array($block->getId(), $matched)) {
                          $this->setText('');
                      } else {
                          $this->setText(
                              $this->_filterProvider->getBlockFilter()
                                          ->setStoreId($storeId)
                                          ->filter($block->getContent())
                          );
                      }
                  }
              }

              unset(self::$_widgetUsageMap[$blockHash]);
              return $this;
            }
            else
            {
              $startDate = $item->getData('start_date');
      				$endDate = $item->getData('end_date');
      				$currentDate = $this->date->gmtDate();
      				if (($currentDate >= $startDate) && ($currentDate <= $endDate))
      				{
                $item = $collection->getFirstItem();

                $relatedBlocks = $this->_ruleFactory->create()
                        ->getRelatedBlocks($item->getId());

                $matched = [];
                foreach ($relatedBlocks as $block) {
                    $matched[] = $block['block_id'];
                }

                if ($blockId) {
                    $storeId = $this->_storeManager->getStore()
                            ->getId();
                    /** @var \Magento\Cms\Model\Block $block */
                    $block = $this->_blockFactory->create();
                    $block->setStoreId($storeId)
                            ->load($blockId);

                    if ($block->isActive()) {
                        if (in_array($block->getId(), $matched)) {
                            $this->setText('');
                        } else {
                            $this->setText(
                                $this->_filterProvider->getBlockFilter()
                                            ->setStoreId($storeId)
                                            ->filter($block->getContent())
                            );
                        }
                    }
                }

                unset(self::$_widgetUsageMap[$blockHash]);
                return $this;
              }
              else
              {
                if ($blockId)
                {
                  $storeId = $this->_storeManager->getStore()->getId();
                  /** @var \Magento\Cms\Model\Block $block */
                  $block = $this->_blockFactory->create();
                  $block->setStoreId($storeId)->load($blockId);
                  if ($block->isActive())
                  {
                    $this->setText(
                        $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent())
                    );
                  }
                }
                unset(self::$_widgetUsageMap[$blockHash]);
                return $this;
              }
            }
          }
        }

        if ($blockId)
        {
          $storeId = $this->_storeManager->getStore()->getId();
          /** @var \Magento\Cms\Model\Block $block */
          $block = $this->_blockFactory->create();
          $block->setStoreId($storeId)->load($blockId);
          if ($block->isActive())
          {
            $this->setText(
                $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent())
            );
          }
        }
        unset(self::$_widgetUsageMap[$blockHash]);
        return $this;
    }
}

























/*
hell;o
*/
