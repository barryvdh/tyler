<?php
declare(strict_types=1);

namespace Bss\OrderRestriction\Model\ResourceModel;

/**
 * Class BundleProduct
 * Get default selection value
 */
class BundleProduct extends AbstractDb
{

    /**
     * Fetch the selection default value
     *
     * @param int $selectionId
     * @return array|false
     */
    public function getSelectionDefaultValue($selectionId)
    {
        try {
            $connection = $this->resource->getConnection();

            $select = $connection->select();

            $select->from(
                ['bundle_selection' => $this->getTable("catalog_product_bundle_selection")],
                []
            )->where('selection_id = ?', $selectionId);

            $select->columns([
                'product_id',
                'qty' => 'selection_qty'
            ]);

            return $connection->fetchRow($select);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }
}
