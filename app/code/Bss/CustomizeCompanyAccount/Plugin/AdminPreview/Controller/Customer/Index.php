<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\AdminPreview\Controller\Customer;

use Bss\AdminPreview\Controller\Customer\Index as BePlugged;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CustomizeCompanyAccount\Model\AdminPreview\LoginFactory as PreviewLoginFactory;
use Bss\CustomizeCompanyAccount\Model\AdminPreview\Login as PreviewLogin;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Index
 * Login as sub-user
 */
class Index
{
    /**
     * @var PreviewLogin
     */
    protected $login;

    /**
     * @var PreviewLoginFactory
     */
    protected $loginFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * Index constructor.
     *
     * @param PreviewLoginFactory $loginFactory
     * @param MessageManager $messageManager
     * @param CustomerSession $customerSession
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        PreviewLoginFactory $loginFactory,
        MessageManager $messageManager,
        CustomerSession $customerSession,
        SubUserRepositoryInterface $subUserRepository
    ) {
        $this->loginFactory = $loginFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->subUserRepository = $subUserRepository;
    }

    /**
     * Login as sub-user
     *
     * @param BePlugged $subject
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(
        BePlugged $subject
    ) {
        $this->initLogin($subject->getRequest());
        if ($this->login && $this->login->getSubUserId()) {
            $subId = $this->login->getSubUserId();
            $subUser = $this->subUserRepository->getById($subId);
            if ($subUser->getSubUserId()) {
                $this->customerSession->setSubUser($subUser);
                $this->messageManager->getMessages(true);
                $this->messageManager->addSuccessMessage(
                    __(
                        "You are logged in as sub-user %1 of %2 company account.",
                        $subUser->getSubUserName(),
                        $this->customerSession->getCustomer()->getName()
                    )
                );
            }
        }
    }

    /**
     * Init login preview
     *
     * @param \Magento\Framework\App\RequestInterface $request
     */
    protected function initLogin($request)
    {
        $secret = $request->getParam('secret');
        if (!$secret) {
            $this->messageManager->addErrorMessage(__('Cannot login to account. No secret key provided.'));
            return;
        }

        /** @var PreviewLogin $login */
        $login = $this->loginFactory->create()->loadBySecret($secret);

        if ($login->getId()) {
            $this->login = $login;
            return;
        }

        $this->messageManager->addErrorMessage(__('Cannot login to account. Secret key is not valid.'));
    }
}
