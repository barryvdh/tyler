<?php
/**
 * Password Protected
 *
 * @category    Addify
 * @package     Addify_PasswordProtected
 * @author      Addify
 * @Email       addifypro@gmail.com
 *
 */
namespace Addify\PasswordProtected\Model\PasswordProtected;

use Addify\PasswordProtected\Model\ResourceModel\PasswordProtected\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blockCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $restrictOrderByCustomerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $restrictOrderByCustomerCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $block) {
            $datas = $block->getData();
            $datas['data']['custom'] =explode(',',$block->getCategoryIds());
            $this->loadedData[$block->getPpId()] =$datas;

        }

        $data = $this->dataPersistor->get('passwordprotected');
        if (!empty($data)) {
            $block = $this->collection->getNewEmptyItem();
            $block->setData($data);
            $this->loadedData[$block->getPpId()] = $block->getData();
            $this->dataPersistor->clear('passwordprotected');
        }

        return $this->loadedData;
    }
}