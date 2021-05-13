<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Plugin\AdminPreview\Adminhtml\Grid;

/**
 * Class CustomerActions
 * Rewrite Customize actions columns customer grid
 */
class CustomerActions extends \Bss\AdminPreview\Plugin\Adminhtml\Grid\CustomerActions
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
        if ($this->_dataHelper->isEnable() &&
            $this->_dataHelper->getCustomerGridLoginColumn() == 'actions' &&
            $this->_authorization->isAllowed('Bss_AdminPreview::login_button') &&
            isset($dataSource['data']['items'])
        ) {
            foreach ($dataSource['data']['items'] as &$item) {
                $id = $item['entity_id'];
                $idField = 'customer_id';

                // If login as sub-user
                if (preg_match("/^sub-[0-9]+/", $item['entity_id'])) {
                    $id = preg_replace(
                        "/^sub-([0-9]+)/",
                        '${1}',
                        $item['entity_id']
                    );
                    $idField = 'sub_user_id';
                }

                $item[$subject->getData('name')]['preview'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'adminpreview/customer/login',
                        [$idField => $id]
                    ),
                    'label' => __('Login'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
