<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\LoginAsCustomer\Adminhtml\Grid;

/**
 * Class CustomerActions
 * Rewrite Customize actions columns customer grid
 */
class CustomerActions extends \Bss\LoginAsCustomer\Plugin\Adminhtml\Grid\CustomerActions
{
    /**
     * Rewrite Customize actions columns customer grid
     *
     * @param \Magento\Customer\Ui\Component\Listing\Column\Actions $subject
     * @param array $dataSource
     * @return array
     */
    public function afterPrepareDataSource(
        \Magento\Customer\Ui\Component\Listing\Column\Actions $subject,
        array $dataSource
    ) {
        if ($this->dataHelper->isEnable() &&
            $this->dataHelper->getCustomerGridLoginColumn() == 'actions' &&
            $this->authorization->isAllowed('Bss_LoginAsCustomer::login_button') &&
            isset($dataSource['data']['items'])
        ) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$subject->getData('name')]['preview'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'loginascustomer/customer/login',
                        ['customer_id' => $item['entity_id']]
                    ),
                    'label' => __('Login'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
