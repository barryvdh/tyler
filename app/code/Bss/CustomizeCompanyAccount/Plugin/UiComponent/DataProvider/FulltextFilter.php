<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;

/**
 * Class FulltextFilter
 * Custom search filter for sub user with customer grid
 */
class FulltextFilter
{
    /**
     * Patterns using for escaping special characters
     */
    private $escapePatterns = [
        '/[@\.]/' => '\_',
        '/([+\-><\(\)~*]+)/' => ' ',
    ];

    /**
     * Fulltext search with sub_user table
     *
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $subject
     * @param mixed $result
     * @param Collection $collection
     * @param Filter $filter
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Select_Exception
     */
    public function afterApply(
        \Magento\Framework\View\Element\UiComponent\DataProvider\FulltextFilter $subject,
        $result,
        Collection $collection,
        Filter $filter
    ) {
        if ($collection instanceof \Bss\CustomizeCompanyAccount\Model\ResourceModel\Grid\Customer\Collection) {
            $searchPart = $collection->getSelect()->getPart('where');
            foreach ($searchPart as &$searchQuery) {
                // Match with query like (MATCH(abc.aaaa)) and replace to add query logic
                $pattern = "/^\((MATCH.*\))\)$/";
                $replacement = sprintf(
                    '(${1} OR MATCH(`sub_user`.sub_name,`sub_user`.sub_email) AGAINST("%s"))',
                    $this->escapeAgainstValue($filter->getValue())
                );
                $searchQuery = preg_replace($pattern, $replacement, $searchQuery);
                $collection->getSelect()->setPart('where', $searchPart);
            }
        }
    }

    /**
     * Escape against value
     *
     * @param string $value
     * @return string
     */
    private function escapeAgainstValue(string $value): string
    {
        return preg_replace(array_keys($this->escapePatterns), array_values($this->escapePatterns), $value);
    }
}
