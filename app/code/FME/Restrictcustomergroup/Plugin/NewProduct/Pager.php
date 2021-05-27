<?php
/**
 * Class for Restrictcustomergroup Plugin Pager
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin\NewProduct;

class Pager
{
  /**
   * Instance of pager block
   *
   * @var \FME\Restrictcustomergroup\Plugin\NewProduct\Pager
   */
  protected $_pager;

  public function aroundGetPagerHtml(
      \Magento\Catalog\Block\Product\Widget\NewWidget $subject,
      \Closure $proceed
  ) {

      // $custom = $subject->getProductCollection();
      // foreach ($custom as $key => $product)
      // {
      //   if ($product->getEntityId() == '14')
      //   {
      //     $custom->removeItemByKey($product->getEntityId());
      //   }
      // }

      // echo count($subject->getProductCollection());
      // exit;

      // echo '<pre>'; print_r($subject->_getProductCollection()->getData());
      // exit;

      echo '<pre>'; print_r(count($subject->_getProductCollection()));
      exit;

      // $totalitem = count($custom->getData());

      if ($subject->showPager())
      {
        if (!$this->_pager)
        {
            $this->_pager = $subject->getLayout()->createBlock(
                \Magento\Catalog\Block\Product\Widget\Html\Pager::class,
                'widget.new.product.list.pager'
            );
            $this->_pager->setUseContainer(true)
                ->setShowAmounts(true)
                ->setShowPerPage(false)
                ->setPageVarName($subject->getData('page_var_name'))
                ->setLimit($subject->getProductsPerPage())
                ->setTotalLimit($subject->getProductsCount())
                ->setCollection($subject->getProductCollection());
        }
        if ($this->_pager instanceof \Magento\Framework\View\Element\AbstractBlock)
        {
          return $this->_pager->toHtml();
        }
      }
      return '';
  }
}
