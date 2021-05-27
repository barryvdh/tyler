<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\Order;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class RefundItemProcessor
 */
class RefundItemProcessor
{
    /**
     * @var \Bss\OrderRestriction\Model\RefundItemFactory
     */
    protected $refundItemFactory;

    /**
     * @var \Bss\OrderRestriction\Api\RefundItemRepositoryInterface
     */
    protected $refundItemRepository;

    /**
     * @var \Magento\Sales\Api\OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    /**
     * RefundItemProcessor constructor.
     *
     * @param \Bss\OrderRestriction\Model\RefundItemFactory $refundItemFactory
     * @param \Bss\OrderRestriction\Api\RefundItemRepositoryInterface $refundItemRepository
     * @param \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
     */
    public function __construct(
        \Bss\OrderRestriction\Model\RefundItemFactory $refundItemFactory,
        \Bss\OrderRestriction\Api\RefundItemRepositoryInterface $refundItemRepository,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository
    ) {
        $this->refundItemFactory = $refundItemFactory;
        $this->refundItemRepository = $refundItemRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    /**
     * @param CreditmemoInterface $creditmemo
     * @param OrderInterface $order
     * @param array $returnToStockItems
     * @return void
     * @throws \Exception
     */
    public function execute($creditmemo, $order, $returnToStockItems)
    {
        $itemsToUpdate = [];
        foreach ($creditmemo->getItems() as $item) {
            $productId = $item->getProductId();
            $orderItem = $this->orderItemRepository->get($item->getOrderItemId());
            $parentItemId = $orderItem->getParentItemId();
            $qty = $item->getQty();
            if ($this->canReturnItem($item, $qty, $parentItemId, $returnToStockItems)) {
                if (isset($itemsToUpdate[$productId])) {
                    $itemsToUpdate[$productId] += $qty;
                } else {
                    $itemsToUpdate[$productId] = $qty;
                }
            }
        }

        if (!empty($itemsToUpdate)) {
            foreach ($itemsToUpdate as $productId => $qty) {
                $refundItem = $this->refundItemRepository->get(
                    $order->getEntityId(),
                    $productId,
                    $order->getCustomerId()
                );

                if ($refundItem->getId()) {
                    $qty += $refundItem->getQty();
                }

                $refundItem->setCustomerId($order->getCustomerId());
                $refundItem->setOrderId($order->getEntityId());
                $refundItem->setProductId($productId);
                $refundItem->setQty($qty);

                $this->refundItemRepository->save($refundItem);
            }
        }
    }

    /**
     * @param \Magento\Sales\Api\Data\CreditmemoItemInterface $item
     * @param int $qty
     * @param int[] $returnToStockItems
     * @param int $parentItemId
     * @return bool
     */
    private function canReturnItem(
        \Magento\Sales\Api\Data\CreditmemoItemInterface $item,
        $qty,
        $parentItemId = null,
        array $returnToStockItems = []
    ) {
        return (in_array($item->getOrderItemId(), $returnToStockItems) || in_array($parentItemId, $returnToStockItems))
            && $qty;
    }
}
