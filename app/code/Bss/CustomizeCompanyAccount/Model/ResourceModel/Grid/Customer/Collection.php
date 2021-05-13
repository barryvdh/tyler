<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Model\ResourceModel\Grid\Customer;

/**
 * Class Collection
 * Join table to display sub-user info
 */
class Collection extends \Bss\CustomerToSubUser\Model\ResourceModel\Customer\Grid\Collection
{

    /**
     * Join table to display sub-user info
     *
     * @return $this|Collection
     * @throws \Zend_Db_Select_Exception
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $customerJoinLeftPartUnion = clone $this->getSelect();
        $customerJoinLeftPartUnion->joinLeft(
            ["sub_user" => $this->getTable("bss_sub_user")],
            "sub_user.sub_email=main_table.email",
            ["sub_name", "sub_email", "customer_id", "sub_id", "sub_status"]
        )->joinLeft(
            ['company' => $this->getMainTable()],
            "`sub_user`.`customer_id`=`company`.`entity_id`",
            ["company_account_name" => "name"]
        )->joinLeft(
            ['sub_user_role' => $this->getTable("bss_sub_role")],
            "`sub_user_role`.`role_id`=`sub_user`.`role_id`",
            ["role_name"]
        );

        $customerJoinRightPartUnion = clone $this->getSelect();

        $customerJoinRightPartUnion->joinRight(
            ["sub_user" => $this->getTable("bss_sub_user")],
            "sub_user.sub_email=main_table.email",
            ["sub_name", "sub_email", "customer_id", "sub_id", "sub_status"]
        )->joinLeft(
            ['company' => $this->getMainTable()],
            "`sub_user`.`customer_id`=`company`.`entity_id`",
            ["company_account_name" => "name"]
        )->joinLeft(
            ['sub_user_role' => $this->getTable("bss_sub_role")],
            "`sub_user_role`.`role_id`=`sub_user`.`role_id`",
            ["role_name"]
        );

        $fromPart = $this->getSelect()->getPart("from");
        if (isset($fromPart['main_table'])) {
            unset($fromPart["main_table"]);
        }
        $this->getSelect()->setPart("from", $fromPart);
        $this->getSelect()->from(
            [
                'main_table' => new \Zend_Db_Expr(sprintf(
                    "(%s UNION %s)",
                    $customerJoinLeftPartUnion,
                    $customerJoinRightPartUnion
                ))
            ],
            []
        );
        $this->getSelect()->columns([
            "name" => $this->getConnection()->getCheckSql(
                "main_table.entity_id IS NULL",
                "main_table.sub_name",
                "main_table.name"
            ),
            "email" => $this->getConnection()->getCheckSql(
                "main_table.entity_id IS NULL",
                "main_table.sub_email",
                "main_table.email"
            ),
            "entity_id" => $this->getConnection()->getCheckSql(
                "main_table.entity_id IS NULL",
                new \Zend_Db_Expr("CONCAT('sub-', main_table.sub_id)"),
                "main_table.entity_id"
            )
        ]);
        $this->getSelect()->joinLeft(
            ['grid_table' => $this->getMainTable()],
            "main_table.entity_id=grid_table.entity_id",
            []
        )->joinLeft(
            ['company' => $this->getMainTable()],
            "main_table.customer_id=company.entity_id",
            []
        )->joinLeft(
            ['sub_user' => $this->getTable("bss_sub_user")],
            "`main_table`.sub_email=`sub_user`.sub_email",
            []
        );

        // add custom columns
        $this->getSelect()->columns([
            'company_account_name' => $this->getConnection()->getCheckSql(
                "main_table.entity_id IS NULL",
                "company.name",
                "NULL"
            ),
            'is_sub_user' => $this->getConnection()->getCheckSql(
                "main_table.entity_id IS NULL",
                "1",
                "0"
            )
        ]);
        $this->getSelect()->columns($this->mappingReplaceFieldsExpr($this->getSelect()));

        return $this;
    }
}
