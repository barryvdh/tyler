<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Controller\Adminhtml\Product;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Quick set order restriction multiple customer
 */
class QuickSet implements HttpPostActionInterface
{
    /**
     * Authorization for manage order restriction
     */
    const ADMIN_RESOURCE = "Magento_Catalog::catalog";

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * QuickSet constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderRuleRepositoryInterface $orderRuleRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Psr\Log\LoggerInterface $logger,
        OrderRuleRepositoryInterface $orderRuleRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->request = $request;
        $this->authorization = $authorization;
        $this->logger = $logger;
        $this->orderRuleRepository = $orderRuleRepository;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();

        if (!$this->authorization->isAllowed(self::ADMIN_RESOURCE)) {
            return $jsonResult->setData([
                'message' => __("You haven't no permission to this action."),
                'success' => false
            ]);
        }
        $success = false;
        $msg = "";

        try {
            $selectedProducts = $this->request->getParam('selected_products');
            $successCount = 0;
            $noneUpdated = [];

            if ($selectedProducts) {
                $requestData = $this->request->getParam('order_restriction');

                foreach ($selectedProducts as $productId) {
                    $result = $this->createOrderRule($productId, $requestData);
                    if (!is_bool($result)) {
                        $noneUpdated[] = $result;
                    }

                    if ($result === true) {
                        $successCount++;
                    }
                }
            }

            if ($successCount) {
                $success = true;
                $msg = __("A total of %1 record(s) have been updated.", $successCount);
            }

            if ($noneUpdated) {
                $success = true;
                $msg .= __(
                    "Products %1 are not be updated. Please review the log.",
                    implode(", ", $noneUpdated)
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        if (!$success) {
            $msg = __("Something went wrong! Please review the log!");
        }

        return $jsonResult->setData([
            'success' => $success,
            'message' => $msg
        ]);
    }

    /**
     * Create order rule by product id and provided request data
     *
     * @param int $productId
     * @param array $requestField
     * @return bool
     */
    private function createOrderRule($productId, $requestField)
    {
        try {
            if (!isset($requestField["sale_allowed_per_month"]) ||
                $requestField["sale_allowed_per_month"] == ""
            ) {
                $requestField["sale_allowed_per_month"] = null;
            }
            $orderRuleData = [
                "product_id" => $productId,
                "sale_qty_per_month" => $requestField["sale_allowed_per_month"],
                "use_config_sale_qty_per_month" => 0
            ];

            return $this->orderRuleRepository->save($orderRuleData);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $productId;
        }
    }

    /**
     * Return true for manual check
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return true;
    }
}
