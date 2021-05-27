<?php
declare(strict_types=1);

namespace Bss\CustomizeForceLogin\Plugin\Cart;

use Bss\CustomizeForceLogin\Helper\Data;
use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Checkout\Controller\Cart\Add as BePlugged;
use Magento\Framework\App\Response\RedirectInterface;

/**
 * Check force login when add to cart
 * Force login when product page is enable
 *
 * @see BePlugged
 */
class Add
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * Add constructor.
     *
     * @param Data $helper
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param RedirectInterface $redirect
     * @param CatalogSession $catalogSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        RedirectInterface $redirect,
        CatalogSession $catalogSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->redirectFactory = $redirectFactory;
        $this->redirect = $redirect;
        $this->catalogSession = $catalogSession;
        $this->messageManager = $messageManager;
    }

    /**
     * Can not add product to cart if is not login
     *
     * @param BePlugged $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        BePlugged $subject,
        \Closure $proceed
    ) {
        if ($this->helper->cantAddToCart()) {
            $resultRedirect = $this->redirectFactory->create();
            $currentUrl = $this->redirect->getRefererUrl();
            $this->catalogSession->setBssCurrentUrl($currentUrl);
            $message = $this->helper->getForceLoginMessage();
            if ($message) {
                $this->messageManager->addErrorMessage($message);
            }
            return $resultRedirect->setPath('customer/account/login');
        }

        return $proceed();
    }
}
