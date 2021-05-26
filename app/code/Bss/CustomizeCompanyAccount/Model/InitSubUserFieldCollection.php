<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Model;

use Magento\Framework\App\ObjectManager;

/**
 * Class InitSubUserFieldCollection
 * Init sub-user field collection
 */
class InitSubUserFieldCollection
{
    protected $hideField = ['token_expires_at', 'token', 'parent_quote_id'];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var SubUserFieldFactory
     */
    protected $subUserFieldFactory;

    /**
     * @var \Magento\Framework\Data\Collection
     */
    protected $collection;

    /**
     * InitSubUserFieldCollection constructor.
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param SubUserFieldFactory $subUserFieldFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection $collection,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Bss\CustomizeCompanyAccount\Model\SubUserFieldFactory $subUserFieldFactory
    ) {
        $this->collection = $collection;
        $this->resourceConnection = $resourceConnection;
        $this->subUserFieldFactory = $subUserFieldFactory;
    }

    /**
     * Init sub-user fields collection
     *
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->collection->count() < 1) {
            foreach ($this->getSubUserFields() as $field) {
                /** @var \Bss\CustomizeCompanyAccount\Model\SubUserField $subUserField */
                $subUserField = $this->subUserFieldFactory->create();
                $subUserField->setField($field);
                $subUserField->setFilterType();
                if (isset($this->getMappingFilterType()[$field])) {
                    $subUserField->setFilterType($this->getMappingFilterType()[$field]);
                }

                if (isset($this->getMappingFieldLabel()[$field])) {
                    $subUserField->setFieldLabel($this->getMappingFieldLabel()[$field]);
                }

                if (isset($this->getMappingFilterOptions()[$field])) {
                    $optionSource = $this->createObject($this->getMappingFilterOptions()[$field]);
                    $subUserField->setFilterOptions($optionSource->toOptionArray());
                }
                $this->collection->addItem($subUserField);
            }
        }

        return $this->collection;
    }

    /**
     * Create object
     *
     * @param string $class
     * @return mixed
     */
    private function createObject(string $class)
    {
        return ObjectManager::getInstance()->create($class);
    }

    /**
     * Get mapping field filter type
     *
     * @return array
     */
    public function getMappingFilterType(): array
    {
        return [
            'created_at' => \Magento\ImportExport\Model\Export::FILTER_TYPE_DATE,
            'token_expires_at' => \Magento\ImportExport\Model\Export::FILTER_TYPE_DATE,
            'sub_status' => \Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT,
            'is_sent_email' => \Magento\ImportExport\Model\Export::FILTER_TYPE_SELECT
        ];
    }

    /**
     * Get mapping filter options
     *
     * @return string[]
     */
    public function getMappingFilterOptions(): array
    {
        return [
            'sub_status' => \Bss\CompanyAccount\Model\Config\Source\EnableDisable::class,
            'is_sent_email' => \Magento\Config\Model\Config\Source\Yesno::class
        ];
    }

    /**
     * Get mapping sub-user field label
     *
     * @return array
     */
    public function getMappingFieldLabel(): array
    {
        return [
            'sub_id' => __("Sub-user ID"),
            'customer_id' => __("Company Account ID"),
            'sub_name' => __("Sub-user Name"),
            'sub_email' => __("Sub-user Email"),
            'sub_status' => __("Sub-user Status"),
            'role_id' => __("Sub-user role ID"),
            'created_at' => __("Sub-user Create Time"),
            'quote_id' => __("Sub-user Quote ID"),
            'quote_status' => __("Sub-user Quote Status"),
            'is_sent_email' => __("Has sent welcome email for Sub-user"),
            'sub_password' => __("Sub-user Password"),
        ];
    }

    /**
     * Get db sub-user fields
     *
     * @return array
     */
    public function getSubUserFields(): array
    {
        $fields = $this->resourceConnection
            ->getConnection()
            ->describeTable(
                $this->resourceConnection->getTableName("bss_sub_user")
            );
        foreach ($this->hideField as $field) {
            if (isset($fields[$field])) {
                unset($fields[$field]);
            }
        }

        return array_keys(
            $fields
        );
    }
}
