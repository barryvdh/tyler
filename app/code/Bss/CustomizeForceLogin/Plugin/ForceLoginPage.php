<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Plugin;

use Bss\ForceLogin\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class ForceLoginPage
 * Override force login specific page, add redirect previous
 */
class ForceLoginPage extends \Bss\ForceLogin\Plugin\ForceLoginPage
{
    /**
     * @var \Bss\CustomizeForceLogin\Helper\Data
     */
    protected $customizeHelper;

    /**
     * ForceLoginPage constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param Session $authSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Bss\CustomizeForceLogin\Helper\Data $customizeHelper
     */
    public function __construct(
        Context $context,
        Data $helperData,
        Session $authSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Bss\CustomizeForceLogin\Helper\Data $customizeHelper
    ) {
        parent::__construct($context, $helperData, $authSession, $httpContext);
        $this->customizeHelper = $customizeHelper;
    }

    /**
     * Add referer to login url
     *
     * @param Action $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function aroundDispatch(Action $subject, callable $proceed, RequestInterface $request)
    {
        $enableLogin = $this->helperData->isEnable();
        $customerLogin = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        $result = $proceed($request);
        $adminSession = $this->authSession->isLoggedIn();
        $resultPage = $result instanceof Page;
        $actionName = $request->getFullActionName();
        $actionName = str_replace("_", "/", $actionName);
        if (!$resultPage || !$enableLogin || $customerLogin || $adminSession
            || in_array($actionName, $this->getIgnoreList())
        ) {
            return $result;
        } else {
            $originalPathInfo = $request->getOriginalPathInfo();
            if ($originalPathInfo && $originalPathInfo != "/") {
                $originalPathInfo = substr_replace($originalPathInfo, "", 0, 1);
            }

            if ($this->checkConfig($actionName, $originalPathInfo)) {
                return $result;
            } else {
                $resultRedirect = $this->resultRedirectFactory->create();
                $message = $this->helperData->getAlertMessage();
                if ($message) {
                    $this->messageManager->addErrorMessage($message);
                }
                return $resultRedirect->setPath(
                    'customer/account/login',
                    $this->customizeHelper->getLoginRefererParam(
                        $this->url->getCurrentUrl()
                    )
                );
            }
        }
    }
}
