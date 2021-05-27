<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Plugin\ForceLogin;

use Magento\Catalog\Model\Session as CatalogSession;

/**
 * Class AfterExecute
 * Set referer param for force login redirect
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class AfterExecute
{
    /**
     * @var CatalogSession
     */
    protected $catalogSession;

    /**
     * @var \Bss\CustomizeForceLogin\Helper\Data
     */
    protected $customizeHelper;

    /**
     * AfterExecute constructor.
     *
     * @param CatalogSession $catalogSession
     * @param \Bss\CustomizeForceLogin\Helper\Data $customizeHelper
     */
    public function __construct(
        CatalogSession $catalogSession,
        \Bss\CustomizeForceLogin\Helper\Data $customizeHelper
    ) {
        $this->catalogSession = $catalogSession;
        $this->customizeHelper = $customizeHelper;
    }

    /**
     * Set referer param for force login redirect
     *
     * @param mixed $subject
     * @param \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAroundExecute(
        $subject,
        $resultRedirect
    ) {
        $isResultRedirect = $resultRedirect instanceof \Magento\Framework\Controller\Result\Redirect;
        if ($isResultRedirect && $currentUrl = $this->catalogSession->getBssCurrentUrl()) {
            $this->catalogSession->unsBssPreviousUrl();
            $resultRedirect->setPath(
                'customer/account/login',
                $this->customizeHelper->getLoginRefererParam($currentUrl)
            );
        }

        return $resultRedirect;
    }
}
