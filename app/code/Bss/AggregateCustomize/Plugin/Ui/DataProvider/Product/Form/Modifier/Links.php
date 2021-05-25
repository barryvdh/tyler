<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Bss\AggregateCustomize\Helper\Data;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links as BePlugged;

/**
 * Class Links
 * Remove links_purchased_separately field, hide some columns in dynamic links
 *
 * @see BePlugged
 */
class Links
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Links constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Remove the links_purchased_separately field
     *
     * @param BePlugged $subject
     * @param array $metaData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterModifyMeta(
        BePlugged $subject,
        array $metaData
    ): array {
        if ($this->helper->isBrandManager()) {
            if (isset($metaData["downloadable"]["children"]["container_links"]
                ["children"]["links_purchased_separately"])
            ) {
                unset($metaData["downloadable"]["children"]["container_links"]
                    ["children"]["links_purchased_separately"]);
                $this->hideDynamicCol($metaData, 'container_link_price');
                $this->hideDynamicCol($metaData, 'container_sample');
                $this->hideDynamicCol($metaData, 'is_shareable', 1);
                $this->hideDynamicCol($metaData, 'max_downloads', 1, true);
            }
        }

        return $metaData;
    }

    /**
     * Hide downloadable link container dynamic row columns
     *
     * @param array $meta
     * @param string $colName
     * @param string|int|null $value
     * @param bool $hideChild
     */
    protected function hideDynamicCol(array &$meta, string $colName, $value = null, bool $hideChild = false)
    {
        if (isset($meta["downloadable"]["children"]["container_links"]["children"]['link'])) {
            $linkMeta = &$meta["downloadable"]["children"]["container_links"]["children"]['link'];
            if (isset($linkMeta['children']['record'])) {
                $recordMeta = &$linkMeta['children']['record'];
                if (isset($recordMeta['children'][$colName])) {
                    $col = &$recordMeta['children'][$colName];
                    $this->hide($col, $value, $hideChild);
                }
            }
        }
    }

    /**
     * Hide provided field
     *
     * @param array $field
     * @param string|int|null $value
     * @param bool $isHideChildren
     */
    protected function hide(array &$field, $value = null, bool $isHideChildren = false)
    {
        $field['arguments']['data']['config']['visible'] = false;
        if ($value !== null && !isset($field['children'])) {
            $field['arguments']['data']['config']['value'] = $value;
        }
        if ($isHideChildren && isset($field['children']) && $value !== null) {
            foreach ($field['children'] as &$child) {
                $this->hide($child, $value);
            }
        }
    }
}
