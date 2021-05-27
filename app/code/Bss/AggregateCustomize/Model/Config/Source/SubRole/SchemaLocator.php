<?php
declare(strict_types=1);
namespace Bss\AggregateCustomize\Model\Config\Source\SubRole;

use Magento\Framework\Module\Dir;

/**
 * Class SchemaLocator
 * Rewrite company schemalocator
 */
class SchemaLocator implements \Magento\Framework\Config\SchemaLocatorInterface
{
    /**
     * Path to corresponding XSD file with validation rules for merged configs
     *
     * @var string
     */
    private $schema;

    /**
     * Path to corresponding XSD file with validation rules for individual configs
     *
     * @var string
     */
    private $schemaFile;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param string|null $moduleName
     */
    public function __construct(\Magento\Framework\Module\Dir\Reader $moduleReader, $moduleName = null)
    {
        if (!$moduleName) {
            $moduleName = "Bss_CompanyAccount";
        }
        $dir = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, $moduleName);
        $this->schema = $dir . '/company_rules.xsd';
        $this->schemaFile = $dir . '/company_rules_file.xsd';
    }

    /**
     * @inheritdoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getPerFileSchema()
    {
        return $this->schemaFile;
    }
}
