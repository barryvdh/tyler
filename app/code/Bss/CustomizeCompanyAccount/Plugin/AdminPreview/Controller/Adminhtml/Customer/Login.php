<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\AdminPreview\Controller\Adminhtml\Customer;

use Bss\AdminPreview\Controller\Adminhtml\Customer\Login as BePlugged;
use Bss\AdminPreview\Plugin\FrontendUrl;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CustomizeCompanyAccount\Model\AdminPreview\LoginFactory as PreviewLoginFactory;
use Bss\CustomizeCompanyAccount\Model\AdminPreview\Login as PreviewLogin;
use Magento\Backend\Model\View\Result\RedirectFactory as BackendRedirectFactory;
use Magento\Framework\Controller\Result\RedirectFactory as FrontendRedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Model\Auth\Session as BackendSession;

/**
 * Class Login
 * Login with sub-user
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class Login
{
    /**
     * @var PreviewLoginFactory
     */
    protected $previewLoginFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var BackendRedirectFactory
     */
    protected $backendRedirectFactory;

    /**
     * @var FrontendRedirectFactory
     */
    protected $frontendRedirectFactory;

    /**
     * @var BackendSession
     */
    protected $backendSession;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FrontendUrl
     */
    protected $frontendUrl;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * Login constructor.
     *
     * @param PreviewLoginFactory $previewLogin
     * @param MessageManager $messageManager
     * @param BackendRedirectFactory $backendRedirectFactory
     * @param FrontendRedirectFactory $frontendRedirectFactory
     * @param BackendSession $backendSession
     * @param StoreManagerInterface $storeManager
     * @param FrontendUrl $frontendUrl
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        PreviewLoginFactory $previewLogin,
        MessageManager $messageManager,
        BackendRedirectFactory $backendRedirectFactory,
        FrontendRedirectFactory $frontendRedirectFactory,
        BackendSession $backendSession,
        StoreManagerInterface $storeManager,
        FrontendUrl $frontendUrl,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->previewLoginFactory = $previewLogin;
        $this->messageManager = $messageManager;
        $this->backendRedirectFactory = $backendRedirectFactory;
        $this->frontendRedirectFactory = $frontendRedirectFactory;
        $this->backendSession = $backendSession;
        $this->storeManager = $storeManager;
        $this->frontendUrl = $frontendUrl;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Login with sub-user
     *
     * @param BePlugged $subject
     * @param callable $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        BePlugged $subject,
        callable $proceed
    ) {
        $request = $subject->getRequest();
        if ($subId = $request->getParam("sub_user_id")) {
            $error = false;
            try {
                $subUser = $this->subUserRepository->getById($subId);
            } catch (\Exception $e) {
                $error = true;
            }

            if (isset($subUser) && !$subUser->getSubUserId()) {
                $error = true;
            }

            if ($error) {
                $this->messageManager->addErrorMessage(
                    __('The sub-user is not exist.')
                );
                return $this->backendRedirectFactory->create()->setPath('customer/index/index');
            }

            $customerId = $subUser->getCompanyCustomerId();
            /** @var PreviewLogin $login */
            $login = $this->previewLoginFactory->create();
            $login->setCustomerId($customerId);
            $login->setSubUserId($subId);
            $login->deleteNotUsed();
            $customer = $login->getCustomer();

            if (!$customer->getId()) {
                $this->messageManager->addErrorMessage(__('The company account is not exist.'));
                return $this->backendRedirectFactory->create()->setPath('customer/index/index');
            }

            $user = $this->backendSession->getUser();
            $login->generate($user->getId());

            $store = $this->storeManager->getStore($customer->getStoreId());
            $url = $this->frontendUrl->getFrontendUrl()->setScope($store);

            $redirectUrl = $url->getUrl(
                'adminpreview/customer/index',
                [
                    'secret' => $login->getSecret(),
                    '_nosid' => true
                ]
            );
            return $this->frontendRedirectFactory->create()->setUrl($redirectUrl);
        }

        return $proceed();
    }
}
