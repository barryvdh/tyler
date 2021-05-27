<?php
/**
 * Class for Restrictcustomergroup Menu
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Plugin;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

class Menu extends \Magento\Theme\Block\Html\Topmenu
{
  /** @var categoriesIdArray  */
  protected $categoriesIdArray = [];

  public function aroundGetHtml(
      \Magento\Theme\Block\Html\Topmenu $subject,
      \Closure $proceed,
      $outermostClass = '',
      $childrenWrapClass = '',
      $limit = 0
  ) {
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $enabledisable = $objectManager->create('FME\Restrictcustomergroup\Helper\Data')->isEnabledInFrontend();
      $subject->_eventManager->dispatch(
            'page_block_html_topmenu_gethtml_before',
            ['menu' => $subject->getMenu(), 'block' => $subject, 'request' => $subject->getRequest()]
        );

      $subject->getMenu()->setOutermostClass($outermostClass);
      $subject->getMenu()->setChildrenWrapClass($childrenWrapClass);

      $currentuserip = $objectManager->create('FME\Restrictcustomergroup\Helper\Data')->getRemoteIPAddress();
      $excludediplist = $objectManager->create('FME\Restrictcustomergroup\Helper\Data')->getExcludedIP();

      if ($enabledisable && !(in_array($currentuserip, $excludediplist)))
      {
        $html = $this->getHtmlRestrictCustomerGroup($subject->getMenu(), $childrenWrapClass, $limit);
      }
      else
      {
        $html = $subject->_getHtml($subject->getMenu(), $childrenWrapClass, $limit);
      }

      $transportObject = new \Magento\Framework\DataObject(['html' => $html]);
      $subject->_eventManager->dispatch(
          'page_block_html_topmenu_gethtml_after',
          ['menu' => $subject->getMenu(), 'transportObject' => $transportObject]
      );
      $html = $transportObject->getHtml();

      // echo '<pre>'; print_r($html);
      // exit;

      return $html;
  }

  public function getHtmlRestrictCustomerGroup(
      \Magento\Framework\Data\Tree\Node $menuTree,
      $childrenWrapClass,
      $limit,
      $colBrakes = []
  ) {
      $this->setCategoriesIdArray();

      $html = '';

      $children = $menuTree->getChildren();

      $parentLevel = $menuTree->getLevel();
      $childLevel = $parentLevel === null ? 0 : $parentLevel + 1;

      $counter = 1;
      $itemPosition = 1;
      $childrenCount = $children->count();

      $parentPositionClass = $menuTree->getPositionClass();
      $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

      /** @var \Magento\Framework\Data\Tree\Node $child */
      foreach ($children as $child)
      {
          if ((sizeof($this->categoriesIdArray) == 0))
          {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                continue;
            }
            $child->setLevel($childLevel);
            $child->setIsFirst($counter == 1);
            $child->setIsLast($counter == $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $currentClass = $child->getClass();

                if (empty($currentClass)) {
                    $child->setClass($outermostClass);
                } else {
                    $child->setClass($currentClass . ' ' . $outermostClass);
                }
            }

            if (is_array($colBrakes) && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                $html .= '</ul></li><li class="column"><ul>';
            }

            $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
            $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                $child->getName()
            ) . '</span></a>' . $this->addSubMenuRestrictCustomerGroup(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            ) . '</li>';
            $itemPosition++;
            $counter++;
          }
          else
          {
            $currentmenuitem = $child->getData();
            if (in_array(substr($currentmenuitem['id'], strrpos($currentmenuitem['id'], '-') + 1), $this->categoriesIdArray) !== false)
            {
              // pass
            }
            else
            {
              if ($childLevel === 0 && $child->getData('is_parent_active') === false)
              {
                  continue;
              }
              $child->setLevel($childLevel);
              $child->setIsFirst($counter == 1);
              $child->setIsLast($counter == $childrenCount);
              $child->setPositionClass($itemPositionClassPrefix . $counter);

              $outermostClassCode = '';
              $outermostClass = $menuTree->getOutermostClass();

              if ($childLevel == 0 && $outermostClass) {
                  $outermostClassCode = ' class="' . $outermostClass . '" ';
                  $currentClass = $child->getClass();

                  if (empty($currentClass)) {
                      $child->setClass($outermostClass);
                  } else {
                      $child->setClass($currentClass . ' ' . $outermostClass);
                  }
              }

              if (is_array($colBrakes) && count($colBrakes) && $colBrakes[$counter]['colbrake']) {
                  $html .= '</ul></li><li class="column"><ul>';
              }

              $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
              $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                  $child->getName()
              ) . '</span></a>' . $this->addSubMenuRestrictCustomerGroup(
                  $child,
                  $childLevel,
                  $childrenWrapClass,
                  $limit
              ) . '</li>';
              $itemPosition++;
              $counter++;
            }
          }
      }

      if (is_array($colBrakes) && count($colBrakes) && $limit) {
          $html = '<li class="column"><ul>' . $html . '</ul></li>';
      }
      return $html;
  }

  public function addSubMenuRestrictCustomerGroup(
    $child,
    $childLevel,
    $childrenWrapClass,
    $limit
  ) {
      $html = '';
      if (!$child->hasChildren()) {
          return $html;
      }

      $colStops = [];
      if ($childLevel == 0 && $limit) {
          $colStops = $this->_columnBrake($child->getChildren(), $limit);
      }

      $html .= '<ul class="level' . $childLevel . ' ' . $childrenWrapClass . '">';
      $html .= $this->getHtmlRestrictCustomerGroup($child, $childrenWrapClass, $limit, $colStops);
      $html .= '</ul>';

      return $html;
  }

  public function setCategoriesIdArray(
  ) {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $helper = $objectManager->create('FME\Restrictcustomergroup\Helper\Data');
    $date = $objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
    $httpcontext = $objectManager->get('Magento\Framework\App\Http\Context');
    $restrictcustomergroup = $objectManager->create('FME\Restrictcustomergroup\Model\RuleFactory')->create();
    $_storeManager = $objectManager->create('Magento\Store\Model\StoreManagerInterface');

    if (!$helper->isEnabledInFrontend())
    {
      return;
    }

    $collection = $restrictcustomergroup->getCollection()
              ->addStoreFilter([$_storeManager->getStore()->getId()], false)
              ->addCustomerGroupFilter($helper->getCustomerGroupId())
              ->addStatusFilter();

    if (sizeof($collection) == 0)
    {
      return;
    }

    $index = 0;
    foreach ($collection as $item)
    {
      if(empty($item->getData('start_date')) || empty($item->getData('end_date')))
      {
        if (!empty($item->getData('categories_ids')))
        {
          $categoriesIdString = $item->getData('categories_ids');
          if (strpos($categoriesIdString, ',') !== false)
          {
            $this->categoriesIdArray =  explode(',',$categoriesIdString);
          }
          else
          {
            $this->categoriesIdArray[$index] = $categoriesIdString;
            $index = $index + 1;
          }
        }
      }
      else
      {
        $startDate = $item->getData('start_date');
        $endDate = $item->getData('end_date');
        $currentDate = $date->gmtDate();
        if (($currentDate >= $startDate) && ($currentDate <= $endDate))
        {
          if (!empty($item->getData('categories_ids')))
          {
            $categoriesIdString = $item->getData('categories_ids');
            if (strpos($categoriesIdString, ',') !== false)
            {
              $this->categoriesIdArray =  explode(',',$categoriesIdString);
            }
            else
            {
              $this->categoriesIdArray[$index] = $categoriesIdString;
              $index = $index + 1;
            }
          }
        }
      }
    }
    $this->categoriesIdArray = array_unique($this->categoriesIdArray);
  }
}
