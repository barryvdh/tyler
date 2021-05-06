<?php
/**
 * Class for Restrictcustomergroup RuleRepositoryInterface
 * @Copyright © FME fmeextensions.com. All rights reserved.
 * @author Arsalan Ali Sadiq <support@fmeextensions.com>
 * @package FME Restrictcustomergroup
 * @license See COPYING.txt for license details.
 */
namespace FME\Restrictcustomergroup\Api;

/**
 * CMS page CRUD interface.
 * @api
 */
interface RuleRepositoryInterface
{

    public function save(\FME\Restrictcustomergroup\Model\RuleInterface $rule);

    public function getById($ruleId);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(\FME\Restrictcustomergroup\Model\RuleInterface $rule);

    public function deleteById($ruleId);
}
