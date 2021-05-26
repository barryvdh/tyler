<?php
declare(strict_types=1);
namespace Bss\ProductSkuPrefix\Model\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class BackendSerialized
 * Backend model for sku prefix config
 */
class BackendSerialized extends Value
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * Tutorial constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Json $json,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->json = $json;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }


    /**
     * Load data when load page config
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $values = [];
        if ($this->getValue()) {
            foreach ($this->json->unserialize($this->getValue()) as $rowId => $row) {
                $row = (array)$row;
                $values[$rowId] = $row;
            }
        }

        $this->setValue($values);
    }


    /**
     * Save data config
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);

        if (is_array($this->getValue())) {
            $this->setValue($this->json->serialize($this->getValue()));
        }

        return parent::beforeSave();
    }
}
