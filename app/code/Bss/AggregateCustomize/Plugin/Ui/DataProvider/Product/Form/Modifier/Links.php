<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\Links as BePlugged;

/**
 * Class Links
 *
 * @see BePlugged
 */
class Links
{
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
        $metaData
    ) {
        if (isset($metaData["downloadable"]["children"]["container_links"]
            ["children"]["links_purchased_separately"])
        ) {
            unset($metaData["downloadable"]["children"]["container_links"]
                ["children"]["links_purchased_separately"]);
        }

        return $metaData;
    }
}
