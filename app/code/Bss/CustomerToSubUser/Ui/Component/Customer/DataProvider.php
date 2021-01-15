<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Ui\Component\Customer;

/**
 * Class DataProvider get list company account customer
 */
class DataProvider extends \Magento\Customer\Ui\Component\DataProvider
{
    /**
     * @inheritDoc
     */
    public function getData()
    {
        $websiteId = $this->request->getParam('website_id');
        if (!$websiteId) {
            return [
                "items" => [],
                "totalRecords" => 0
            ];
        }
        $this->addFilter(
            $this->filterBuilder
                ->setField('website_id')
                ->setValue($websiteId)
                ->create()
        );
        $this->addFilter(
            $this->filterBuilder
                ->setField('bss_is_company_account')
                ->setValue(1)
                ->create()
        );

        return parent::getData();
    }
}
