<?php
declare(strict_types=1);
namespace Bss\CustomizeAmastyRolePermissions\Plugin\Integration\Helper;

/**
 * Modified the resources data
 */
class Data
{
    /**
     * Stored the amasty resources data
     *
     * @var array
     */
    private $amastyResources;

    /**
     * Remove the Amasty Rolepermissions from system section
     *
     * @param \Magento\Integration\Helper\Data $subject
     * @param array $resources
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMapResources(
        \Magento\Integration\Helper\Data $subject,
        $resources
    ) {
        // Remove the amasty resources from the system section
        $this->resourceNodeProcessingRecursive(
            $resources,
            "Amasty_Rolepermissions::permissions",
            [$this, "removeAmastyResources"]
        );

        if (!$this->amastyResources) {
            return $resources;
        }

        // Move the amasty resources to products resource section
        $this->resourceNodeProcessingRecursive(
            $resources,
            "Magento_Catalog::products",
            [$this, "moveAmastyResources"]
        );

        return $resources;
    }

    /**
     * Move amasty resources to products resource process recursion
     *
     * @param array $childsResources
     * @param string $nodeId
     * @param callable $proceed
     */
    protected function resourceNodeProcessingRecursive(&$childsResources, $nodeId, callable $proceed)
    {
        foreach ($childsResources as $index => &$resource) {
            if (isset($resource["attr"]["data-id"]) &&
                $resource["attr"]["data-id"] == $nodeId
            ) {
                $proceed(
                    [
                        'currentResource' => &$resource,
                        'parentResources' => &$childsResources,
                        'index' => $index
                    ]
                );
            }

            if (isset($resource['children'])) {
                $this->resourceNodeProcessingRecursive($resource['children'], $nodeId, $proceed);
            }
        }
    }

    /**
     * Remove from system resources section and stored to the amastyResources variable.
     *
     * This method be called as parameter
     *
     * @see resourceNodeProcessingRecursive
     * @param array $params
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function removeAmastyResources($params)
    {
        $parentResources = &$params["parentResources"];
        $index = $params["index"];
        $this->amastyResources = $parentResources[$index];
        unset($parentResources[$index]);
        $parentResources = array_values($parentResources);
    }

    /**
     * Add amasty resources to products resources
     *
     * This method be called as parameter
     *
     * @see resourceNodeProcessingRecursive
     * @param array $params
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function moveAmastyResources($params)
    {
        $resource = &$params['currentResource'];
        $resource['children'][] = $this->amastyResources;
    }
}
