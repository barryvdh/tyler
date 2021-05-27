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
 * @package    Bss_BrandCategoryLevel
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\BrandCategoryLevel\Plugin;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TopMenu
 * That custom display only level 2 sub categories and below
 * Bss\BrandCategoryLevel\Plugin
 */
class TopMenu
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * TopMenu constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get brand category
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @throws NoSuchEntityException
     * @suppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        //replace with the id of your store. To target only specific store only,
        //here we fixed for default store which return ID 1
        $storeIds = [1];
        $currentStoreId = $this->storeManager->getStore()->getId();
        if (in_array($currentStoreId, $storeIds)) {
            $menu = $subject->getMenu();
            $newMenuItems = [];
            $firstLevel = $menu->getChildren();
            foreach ($firstLevel as $menuItem) {
                /** @var Node $menuItem */
                //check if menu is a category
                if (strpos($menuItem->getId(), 'category-node-') === 0) {
                    //get all child nodes (second level) and save them in an array
                    $subItems = $menuItem->getChildren();
                    foreach ($subItems as $subItem) {
                        $newMenuItems[] = $subItem;
                    }

                } else { //if menu item is not a category, leave it in place
                    $newMenuItems[] = $menuItem;
                }
            }

            // Sort order by name
            usort($newMenuItems, [$this, "sort"]);
            //remove all menu items
            foreach ($firstLevel as $childNode) {
                $menu->removeChild($childNode);
            }
            //put new menu items back;
            foreach ($newMenuItems as $newMenuItem) {
                $menu->addChild($newMenuItem);
            }
        }
    }

    /**
     * Node compare
     *
     * @param Node $node1
     * @param Node $node2
     * @return int
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function sort(
        \Magento\Framework\Data\Tree\Node $node1,
        \Magento\Framework\Data\Tree\Node $node2
    ) {
        $al = strtolower($node1->getName());
        $bl = strtolower($node2->getName());
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }
}
