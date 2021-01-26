<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Plugin\Model;

use Bss\OrderRestriction\Helper\ConfigProvider;
use Bss\OrderRestriction\Helper\OrderRuleValidation;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Check whether the account is valid for ordering or not
 */
class ValidateTheCustomerBeforeAddToCart
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRuleValidation
     */
    private $orderRuleValidation;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * ValidateTheCustomerBeforeAddToCart constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderRuleValidation $orderRuleValidation
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRuleValidation $orderRuleValidation,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ConfigProvider $configProvider
    ) {
        $this->logger = $logger;
        $this->orderRuleValidation = $orderRuleValidation;
        $this->jsonFactory = $jsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->configProvider = $configProvider;
    }

    /**
     * Check whether the account is valid for ordering or not
     *
     * If the qty update is valid then update, if not fallback to previous qty
     *
     * @param $subject
     * @param callable $proceed
     * @return callable|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function aroundExecute(
        $subject,
        callable $proceed
    ) {
        try {
            if (!$this->configProvider->isEnabled()) {
                return $proceed();
            }

            /** @var \Magento\Framework\App\RequestInterface $request */
            $request = $subject->getRequest();
            $qty = $request->getParam('qty');

            // side bar update -> ok
            if ($sideBarUpdateRequestQty = $request->getParam('item_qty')) {
                $qty = $sideBarUpdateRequestQty;
                $itemId = $request->getParam('item_id');
            }

            // Add all from wish list -> oke
            if (is_array($qty)) {
                $qty = array_sum(array_values($qty));
            }

            $isUpdateAll = false;
            // UpdatePost action
            if ($cartRequestParams = $request->getParam('cart')) {
                $qty = array_sum(array_column(array_values($cartRequestParams), 'qty'));
                $isUpdateAll = true;
            }

            // normal update
            if (!isset($itemId)) {
                $itemId = $request->getParam('id');
            }

            if (!$qty) {
                $qty = 1;
            }

            $canPlace = $this->orderRuleValidation->canPlaceOrder(null, $qty, $itemId, $isUpdateAll);

            if (empty($canPlace)) {
                return $proceed();
            }
            $this->messageManager->addErrorMessage(implode(", ", $canPlace));
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        if ($subject->getRequest()->getModuleName() === "wishlist" ||
            !$subject->getRequest()->isAjax()
        ) {
            return $this->redirectFactory->create()->setPath('*/*');
        }

        return $this->jsonFactory->create()->setData([
            'bss_is_restricted' => true
        ]);
    }
}
