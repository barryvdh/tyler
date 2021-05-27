<?php
declare(strict_types=1);
namespace Bss\CustomizeCompanyAccount\Model\AdminPreview;

/**
 * Class Login
 * Add sub-user id
 */
class Login extends \Bss\AdminPreview\Model\Login
{
    /**
     * Set sub-user id
     *
     * @param int $id
     * @return Login
     */
    public function setSubUserId($id)
    {
        return $this->setData('sub_user_id', $id);
    }

    /**
     * Get subuser id
     *
     * @return int
     */
    public function getSubUserId()
    {
        return $this->getData('sub_user_id');
    }

    /**
     * Add sub_user_id to object data
     *
     * @param int $adminId
     * @return \Bss\AdminPreview\Model\Login|Login
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate($adminId)
    {
        return $this->setData([
            'customer_id' => $this->getCustomerId(),
            'admin_id' => $adminId,
            'secret' => $this->_random->getRandomString(64),
            'used' => 0,
            'created_at' => $this->_dateTime->gmtTimestamp(),
            'sub_user_id' => $this->getSubUserId()
        ])->save();
    }

    /**
     * Load login preview by secret
     *
     * @param string $secret
     * @return \Magento\Framework\DataObject
     */
    public function loadBySecret($secret)
    {
        return $this->getCollection()
            ->addFieldToFilter('secret', $secret)
            ->addFieldToFilter('used', 1)
            ->addFieldToFilter('created_at', ['gt' => $this->getDateTimePoint()])
            ->setPageSize(1)
            ->getLastItem();
    }
}
