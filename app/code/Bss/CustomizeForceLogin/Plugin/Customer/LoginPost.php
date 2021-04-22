<?php
declare(strict_types=1);
namespace Bss\CustomizeForceLogin\Plugin\Customer;

use Bss\ForceLogin\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Url\DecoderInterface;

/**
 * Class LoginPost
 * Rewrite redirect after login
 */
class LoginPost extends \Bss\ForceLogin\Plugin\Customer\LoginPost
{
    /**
     * @var \Bss\CustomizeForceLogin\Helper\Data
     */
    protected $customizeHelper;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * LoginPost constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param Data $helperData
     * @param Session $customerSession
     * @param \Bss\CustomizeForceLogin\Helper\Data $customizeHelper
     * @param DecoderInterface $urlDecoder
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        Data $helperData,
        Session $customerSession,
        \Bss\CustomizeForceLogin\Helper\Data $customizeHelper,
        DecoderInterface $urlDecoder
    ) {
        $this->customizeHelper = $customizeHelper;
        $this->urlDecoder = $urlDecoder;
        parent::__construct($context, $registry, $helperData, $customerSession);
    }

    /**
     * Return default
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $resultRedirect
    ) {
        if (!$this->helperData->isEnable() || !$this->customerSession->isLoggedIn()) {
            return $resultRedirect;
        }

        if ($refererParam = $this->customizeHelper->getLoginRefererParam(
            $subject->getRequest()->getParam(\Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME),
            false
        )) {
            return $resultRedirect->setUrl(
                $this->urlDecoder->decode($refererParam[\Magento\Customer\Model\Url::REFERER_QUERY_PARAM_NAME])
            );
        }

        return $resultRedirect->setPath(
            $this->customizeHelper->getRedirectUrl()
        );
    }
}
