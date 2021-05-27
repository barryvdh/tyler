<?php
declare(strict_types=1);
namespace Bss\CustomerToSubUser\Model\ResourceModel\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection as CustomerGridCollection;

/**
 * Class Collection - Link to the `bss_sub_user` table to check if`
 * customer` is `sub-user` then` load` the address information of `company account` instead.
 */
class Collection extends CustomerGridCollection
{
    /**
     * @var string[]
     */
    protected $replaceFields = [
        "shipping_full",
        "billing_full",
        "billing_firstname",
        "billing_lastname",
        "billing_telephone",
        "billing_postcode",
        "billing_country_id",
        "billing_region",
        "billing_street",
        "billing_city",
        "billing_fax",
        "billing_vat_id",
        "billing_company",
        "website_id",
        "ca_company_website",
        "ca_company_name"
    ];

    /**
     * Mapping the billing address column in grid
     *
     * If the customer is sub-user then get the company account billing address instead
     *
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    protected function mappingReplaceFieldsExpr(\Magento\Framework\DB\Select $select): array
    {
        $data = [];
        $connection = $select->getConnection();
        foreach ($this->replaceFields as $field) {
            if ($connection->tableColumnExists($this->getMainTable(), $field)) {
                $data[$field] = $connection->getCheckSql(
                    '`main_table`.entity_id IS NULL',
                    "`company`.$field",
                    "`main_table`.$field"
                );
            }
        }

        return $data;
    }
}
