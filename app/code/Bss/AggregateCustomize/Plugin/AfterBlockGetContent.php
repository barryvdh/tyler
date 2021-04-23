<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin;

/**
 * Class AfterBlockGetContent
 * Add fixed css for pointer-events none
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterBlockGetContent
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
     * BeforeBlockGetContent constructor.
     *
     * @param \Bss\AdminPreview\Helper\Data $dataHelper
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     */
    public function __construct(
        \Bss\AdminPreview\Helper\Data $dataHelper,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
    ) {
        $this->dataHelper = $dataHelper;
        $this->cookieManager = $cookieManager;
    }

    /**
     * Add style pointer-events property
     *
     * @param \Magento\Cms\Model\Block $subject
     * @param string $result
     * @return mixed|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetContent(
        \Magento\Cms\Model\Block $subject,
        $result
    ) {
        if ($this->cookieManager->getCookie('adminLogged') &&
            $this->dataHelper->isEnable() &&
            $this->dataHelper->showLinkFrontend('staticblock')
        ) {
            $result .= $this->getStyleHtml();
        }

        return $result;
    }

    /**
     * Get fixed style css
     *
     * @return string
     */
    public function getStyleHtml()
    {
        return "<style>.bss-adminpreview-backend{pointer-events:all;}</style>";
    }
}
