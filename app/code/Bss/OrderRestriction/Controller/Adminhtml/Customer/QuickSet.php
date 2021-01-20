<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Controller\Adminhtml\Customer;

use Bss\OrderRestriction\Helper\CreateOrderRuleByCustomerId;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Quick set order restriction multiple customer
 */
class QuickSet extends Action implements HttpPostActionInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CreateOrderRuleByCustomerId
     */
    private $createOrderRuleByCustomerId;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Context $context,
        CreateOrderRuleByCustomerId $createOrderRuleByCustomerId,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->logger = $logger;
        $this->createOrderRuleByCustomerId = $createOrderRuleByCustomerId;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();
        $success = false;
        $msg = "";

        try {
            $selectedCustomers = $this->getRequest()->getParam('selected_customers');
            $successCount = 0;
            $noneUpdated = [];

            if ($selectedCustomers) {
                $requestData = $this->getRequest()->getParam('order_restriction');

                foreach ($selectedCustomers as $customerId) {
                    $result = $this->createOrderRuleByCustomerId->execute(
                        $customerId,
                        $requestData
                    );
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
                    "Customers %1 are not allowed to update because they are sub-accounts",
                    implode(", ", $noneUpdated)
                );
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $msg = __("Something went wrong! Please review the log!");
        }

        return $jsonResult->setData([
            'success' => $success,
            'message' => $msg
        ]);
    }
}
