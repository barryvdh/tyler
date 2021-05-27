<?php
declare(strict_types=1);

namespace Bss\CustomizeCompanyAccount\Ui\Component\Listing\Column;

/**
 * Class Actions
 */
class Actions extends \Magento\Customer\Ui\Component\Listing\Column\Actions
{
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (preg_match("/^sub-[0-9]+/", $item['entity_id']) &&
                    isset($item[$this->getData('name')]['edit'])
                ) {
                    $subId = preg_replace(
                        "/^sub-([0-9]+)/",
                        '${1}',
                        $item['entity_id']
                    );
                    $item[$this->getData('name')]['edit']['href'] = '#';
                    $item[$this->getData('name')]['edit']['callback'] = [
                        [
                            'provider' => 'customer_listing.customer_listing.'
                                . 'bss_company_account_update_sub_user.'
                                . 'update_bss_companyaccount_customer_subuser_form_loader',
                            'target' => 'destroyInserted',
                        ],
                        [
                            'provider' => 'customer_listing.customer_listing.'
                                . 'bss_company_account_update_sub_user',
                            'target' => 'openModal',
                        ],
                        [
                            'provider' => 'customer_listing.customer_listing.'
                                . 'bss_company_account_update_sub_user.'
                                . 'update_bss_companyaccount_customer_subuser_form_loader',
                            'target' => 'render',
                            'params' => [
                                'sub_id' => $subId,
                            ],
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
