<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model;

use Bss\OrderRestriction\Api\OrderRuleRepositoryInterface;
use Bss\OrderRestriction\Model\ResourceModel\OrderRule as OrderRuleResource;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;

/**
 * Repository class
 */
class OrderRuleRepository implements OrderRuleRepositoryInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OrderRuleResource
     */
    private $orderRuleResource;

    /**
     * @var OrderRuleFactory
     */
    private $orderRuleFactory;

    /**
     * OrderRuleRepository constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param OrderRuleResource $orderRuleResource
     * @param OrderRuleFactory $orderRuleFactory
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        OrderRuleResource $orderRuleResource,
        OrderRuleFactory $orderRuleFactory
    ) {
        $this->logger = $logger;
        $this->orderRuleResource = $orderRuleResource;
        $this->orderRuleFactory = $orderRuleFactory;
    }

    /**
     * @inheritDoc
     */
    public function getByProductId($productId)
    {
        $orderRule = $this->orderRuleFactory->create();
        try {
            $this->orderRuleResource->load($orderRule, $productId, "product_id");

            return $orderRule;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $orderRule;
        }
    }

    /**
     * @inheritDoc
     */
    public function save($orderRule)
    {
        try {
            if (is_array($orderRule)) {
                if (!isset($orderRule["product_id"])) {
                    throw new InputException(__("The \"product_id\" field must be defined."));
                }
                $oderRuleData = $orderRule;
                $orderRule = $this->getByProductId($oderRuleData["product_id"]);
                $orderRule->setProductId($oderRuleData["product_id"]);
                $orderRule->setUseConfig($oderRuleData["use_config_sale_qty_per_month"] ?? 1);
                $orderRule->setSaleQtyPerMonth($oderRuleData["sale_qty_per_month"] ?? null);
            }
            $this->orderRuleResource->save($orderRule);

            return true;
        } catch (InputException $e) {
            throw $e;
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new CouldNotSaveException(__("Something went wrong! Please review the log!"));
        }
    }
}
