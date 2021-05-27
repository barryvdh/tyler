<?php
declare(strict_types=1);

namespace Bss\AggregateCustomize\Model\Config\Source\SubRole;

/**
 * Class Converter
 * Add remove attribute to node
 */
class Converter extends \Bss\CompanyAccount\Model\Config\Source\SubRole\Converter
{
    /**
     * Add remove attribute to node
     *
     * @param \DOMNode $resourceNode
     * @return array
     * @throws \Exception
     */
    protected function _convertRuleNode(\DOMNode $resourceNode)
    {
        $nodeData = parent::_convertRuleNode($resourceNode);
        $resourceAttributes = $resourceNode->attributes;
        $removeAttr = $resourceAttributes->getNamedItem('remove');

        if ($removeAttr && $removeAttr->nodeValue == "true") {
            $nodeData['remove'] = true;
        }

        return $nodeData;
    }
}
