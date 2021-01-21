<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Helper;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;

/**
 * Class Create Order Rule By CustomerId and Data
 */
class CreateOrderRuleByCustomerId
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRuleRepositoryInterface
     */
    private $orderRuleRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var \Bss\CustomerToSubUser\Model\CompanyAccountManagement
     */
    private $companyAccountManagement;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRuleRepositoryInterface $orderRuleRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerToSubUser\Model\CompanyAccountManagement $companyAccountManagement
    ) {
        $this->logger = $logger;
        $this->orderRuleRepository = $orderRuleRepository;
        $this->customerRepository = $customerRepository;
        $this->companyAccountManagement = $companyAccountManagement;
    }
    /**
     * Order rule execution
     *
     * @param int $customerId
     * @param array|\Magento\Framework\App\RequestInterface $requestData
     * @return bool|int
     */
    public function execute($customerId, $requestData)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $subUser = $this->companyAccountManagement->getCompanyAccountBySubEmail(
                $customer->getEmail(),
                $customer->getWebsiteId()
            );

            if ($subUser->getSubUser()->getSubUserId()) {
                return $customerId;
            }

            if ($requestData instanceof \Magento\Framework\App\RequestInterface) {
                $requestData = $requestData->getParam("order_restriction");
            }
            if ($requestData) {
                // If not set value then put is NULL in DB
                array_walk(
                    $requestData,
                    function (&$param) {
                        if ($param == "") {
                            $param = null;
                        }
                    }
                );
                $orderRule = $this->orderRuleRepository->get($requestData["entity_id"] ?? null);

                if ($orderRule->getOrdersPerMonth() != $requestData['orders_per_month'] ||
                    $orderRule->getQtyPerOrder() != $requestData['qty_per_order']
                ) {
                    $orderRule->setCustomerId($customerId);
                    $orderRule->setOrdersPerMonth($requestData['orders_per_month']);
                    $orderRule->setQtyPerOrder($requestData['qty_per_order']);

                    $this->orderRuleRepository->save($orderRule);
                }

                return true;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
