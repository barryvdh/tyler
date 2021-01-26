<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Ui\Component\Address\Listing;

use Magento\Customer\Model\ResourceModel\Address\Grid\CollectionFactory;
use Magento\Directory\Model\CountryFactory;

/**
 * Class DataProvider
 */
class DataProvider extends \Magento\Customer\Ui\Component\Listing\Address\DataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var CountryFactory
     */
    private $countryDirectory;

    /**
     * DataProvider constructor.
     *
     * @param $name
     * @param $primaryFieldName
     * @param $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param CountryFactory $countryFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        CountryFactory $countryFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->countryDirectory = $countryFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $request,
            $countryFactory,
            $meta,
            $data
        );
    }

    /**
     * Load addresses by company account and customer
     *
     * @return array
     */
    public function getData(): array
    {
        $collection = $this->getCollection();
        $data['items'] = [];
        $parentIdRequestField = $this->request->getParam('parent_id');
        if ($parentIdRequestField && $this->request->getParam('company_account_id')) {
            $parentIdRequestField = $this->request->getParam('company_account_id');
        }
        $collection->addFieldToFilter('parent_id', $parentIdRequestField);
        $data = $collection->toArray();
        foreach ($data['items'] as $key => $item) {
            if (isset($item['country_id']) && !isset($item['country'])) {
                $data['items'][$key]['country'] = $this->countryDirectory->loadByCode($item['country_id'])->getName();
            }
        }

        return $data;
    }
}
