<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Block\Adminhtml\Edit\SubUser;

use Bss\CompanyAccount\Block\Adminhtml\Edit\SubUser\GenericButton;
use Bss\CompanyAccount\Ui\Component\Listing\SubUser\Column\Actions;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteBtn extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getSubId()) {
            $data = [
                'label' => __('Delete'),
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'customize_ca_customer_subuser_form.'
                                        . 'customize_ca_customer_subuser_form',
                                    'actionName' => 'deleteSubUser',
                                    'params' => [
                                        $this->getDeleteUrl(),
                                    ],

                                ]
                            ],
                        ],
                    ],
                ],
                'sort_order' => 20
            ];
        }
        return $data;
    }

    /**
     * Get delete button url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            Actions::CUSTOMER_SUB_USER_PATH_DELETE,
            ['customer_id' => $this->getCustomerId(), 'id' => $this->getSubId()]
        );
    }
}
