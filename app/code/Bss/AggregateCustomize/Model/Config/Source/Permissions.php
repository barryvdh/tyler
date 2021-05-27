<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Model\Config\Source;

/**
 * Class Permissions
 * Remove the node
 */
class Permissions extends \Bss\CompanyAccount\Model\Config\Source\Permissions
{
    /**
     * Map rule data to tree js. Rewrite: remove the node
     *
     * @param array $rules
     * @return array
     */
    protected function mapRules($rules)
    {
        $output = [];
        foreach ($rules as $rule) {
            if (isset($rule['remove']) && $rule['remove'] == true) {
                continue;
            }
            $item = [];
            $item['attr']['data-id'] = $rule['value'];
            $text = __($rule['label']);
            if ((int) $rule['value'] > 0) {
                $text .= " (" . $rule['value'] . ")";
            }
            $item['data'] = $text;
            $item['children'] = [];
            if (isset($rule['children'])) {
                $item['state'] = 'open';
                $item['children'] = $this->mapRules($rule['children']);
            }
            $output[] = $item;
        }
        return $output;
    }

    /**
     * Get mapped rules data array for tree js
     *
     * @return array
     */
    public function mappedDataArray()
    {
        $output = parent::mappedDataArray();
        $output['data'] = __("Company Account");

        return $output;
    }
}
