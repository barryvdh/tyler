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
 * @package    Bss_AdminPreview
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdminPreview\Plugin\Adminhtml;

class BeforeBlockGetContent
{

    /**
     * @var \Bss\AdminPreview\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * BeforeBlockGetContent constructor.
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Backend\Helper\Data $backendHelper
     */
    public function __construct(
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Backend\Helper\Data $backendHelper
    )
    {
        $this->dataHelper = $dataHelper;
        $this->layoutFactory = $layoutFactory;
        $this->cookieManager = $cookieManager;
        $this->backendHelper = $backendHelper;
    }

    /**
     * @param \Magento\Cms\Model\Block $subject
     * @param $result
     * @return string
     */
    public function afterGetContent(
        \Magento\Cms\Model\Block $subject, $result
    )
    {
        $adminLogged = $this->cookieManager->getCookie('adminLogged');
        if ($adminLogged && $this->dataHelper->isEnable() && $this->dataHelper->showLinkFrontend('staticblock')) {
            $blockId = $subject->getId();
            $blockUrl = $this->backendHelper->getUrl('adminpreview/edit/redirect', [
                'type' => 'cms_block', 'block_id' => $blockId
            ]);
            $urlHtml = $this->layoutFactory->create()
                ->createBlock('Bss\AdminPreview\Block\Preview')->assign('url', $blockUrl)
                ->setTemplate('Bss_AdminPreview::frontend_preview_staticblock.phtml')->toHtml();
            return $urlHtml . $result;
        } else {
            return $result;
        }
    }
}