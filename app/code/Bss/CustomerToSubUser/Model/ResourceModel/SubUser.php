<?php
declare(strict_types=1);

namespace Bss\CustomerToSubUser\Model\ResourceModel;

use Psr\Log\LoggerInterface;
use Bss\CompanyAccount\Model\SubUserFactory;

/**
 * Class SubUser
 */
class SubUser
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Bss\CompanyAccount\Model\ResourceModel\SubUser
     */
    private $subUserResource;

    /**
     * @var SubUserFactory
     */
    private $subUserFactory;

    /**
     * SubUser constructor.
     *
     * @param LoggerInterface $logger
     * @param \Bss\CompanyAccount\Model\ResourceModel\SubUser $subUserResource
     * @param SubUserFactory $subUserFactory
     */
    public function __construct(
        LoggerInterface $logger,
        \Bss\CompanyAccount\Model\ResourceModel\SubUser $subUserResource,
        SubUserFactory $subUserFactory
    ) {
        $this->logger = $logger;
        $this->subUserResource = $subUserResource;
        $this->subUserFactory = $subUserFactory;
    }

    /**
     * Get subuser by email and website id
     *
     * @param string $email
     * @param int $websiteId
     * @return \Bss\CompanyAccount\Model\SubUser
     */
    public function getSubUserByEmailAndWebsiteId($email, $websiteId)
    {
        /** @var \Bss\CompanyAccount\Model\SubUser $subUser */
        $subUser = $this->subUserFactory->create();
        try {
            $select = $this->getConnection()->select();
            $select->from(
                ['sub_user' => $this->subUserResource->getTable(\Bss\CompanyAccount\Model\ResourceModel\SubUser::TABLE)],
                ['sub_id']
            )->joinInner(
                ['customer' => $this->subUserResource->getTable('customer_entity')],
                'sub_user.customer_id = customer.entity_id'
            )->where('website_id = ?', $websiteId)->where('sub_email = ?', $email);

            $subId = $this->getConnection()->fetchOne($select);

            if ($subId) {
                $this->subUserResource->load($subUser, $subId);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $subUser;
    }

    /**
     * Get connection
     *
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->subUserResource->getConnection();
    }
}
