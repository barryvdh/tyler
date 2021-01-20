<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Observer;

use Bss\OrderRestriction\Helper\CreateOrderRuleByCustomerId;
use Magento\Framework\Event\Observer;

/**
 * Save the order rule if the request contains, after save customer in admin
 */
class SaveOrderRule implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var CreateOrderRuleByCustomerId
     */
    private $createOrderRuleByCustomerId;

    // @codingStandardsIgnoreLine
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request,
        CreateOrderRuleByCustomerId $createOrderRuleByCustomerId
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->createOrderRuleByCustomerId = $createOrderRuleByCustomerId;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        try {
            $this->createOrderRuleByCustomerId->execute(
                $this->request->getParam('customer')['entity_id'],
                $this->request->getParam('order_restriction')
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
