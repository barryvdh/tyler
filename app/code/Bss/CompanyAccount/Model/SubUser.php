<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUser as ResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SubUser
 *
 * @package Bss\CompanyAccount\Model
 */
class SubUser extends AbstractModel implements SubUserInterface
{
    /**
     * Init SubUser model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * Retrieve label of sub-user status
     *
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getSubUserStatus() === \Bss\CompanyAccount\Model\Config\Source\EnableDisable::ENABLE ?
            __('Enable') : __('Disable');
    }

    /**
     * Sub-user can access
     *
     * @param int $value
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterface $role
     * @return bool
     */
    public function canAccess($value, $role)
    {
        return empty($role->getRoleType()) ? false : in_array(
            $value,
            explode(',', $role->getRoleType())
        );
    }

    /**
     * Get quote id
     *
     * @return int|mixed
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * Set quote id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setQuoteId(int $id)
    {
        return $this->setData(self::QUOTE_ID, $id);
    }

    /**
     * Get parent quote id
     *
     * @return int|mixed
     */
    public function getParentQuoteId()
    {
        return $this->getData(self::PARENT_QUOTE_ID);
    }

    /**
     * Set parent quote id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setParentQuoteId(int $id)
    {
        return $this->setData(self::PARENT_QUOTE_ID, $id);
    }

    /**
     * Get create time
     *
     * @return mixed|string
     */
    public function getCreateTime()
    {
        return $this->getData(self::CREATE_AT);
    }

    /**
     * Set create time
     *
     * @param string $time
     * @return SubUser|void
     */
    public function setCreateTime(string $time)
    {
        return $this->setData(self::CREATE_AT, $time);
    }

    /**
     * Get identifier
     *
     * @return int|mixed
     */
    public function getSubUserId()
    {
        return (int)$this->getData(self::ID);
    }

    /**
     * Set sub user id
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setSubUserId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * Get associate company customer id
     *
     * @return int
     */
    public function getCompanyCustomerId()
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * Associate to a company account
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setCompanyCustomerId($id)
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    /**
     * Get sub user name
     *
     * @return mixed|string
     */
    public function getSubUserName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * Set sub user's name
     *
     * @param string $name
     * @return mixed|void
     */
    public function setSubUserName(string $name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Get sub user's email
     *
     * @return mixed|string
     */
    public function getSubUserEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email for sub user
     *
     * @param string $email
     * @return SubUser|void
     */
    public function setSubUserEmail(string $email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get sub user's password
     *
     * @return mixed|string
     */
    public function getSubUserPassword()
    {
        return $this->getData(self::PASSWORD);
    }

    /**
     * Set password for sub user
     *
     * @param string $password
     * @return SubUser|void
     */
    public function setSubUserPassword(string $password)
    {
        return $this->setData(self::PASSWORD, $password);
    }

    /**
     * Get sub user status
     *
     * @return int|mixed
     */
    public function getSubUserStatus()
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * Set sub user status
     *
     * @param int $status
     * @return SubUser|void
     */
    public function setSubUserStatus(int $status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get token
     *
     * @return mixed|string
     */
    public function getSubUserToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * Set token
     *
     * @param string $token
     * @return SubUser|void
     */
    public function setSubUserToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get associated role
     *
     * @return int|mixed
     */
    public function getRelatedRoleId()
    {
        return $this->getData(self::ROLE_ID);
    }

    /**
     * Associate sub user to role
     *
     * @param int $id
     * @return SubUser|void
     */
    public function setRoleId(int $id)
    {
        return $this->setData(self::ROLE_ID, $id);
    }

    /**
     * Get token expires time
     *
     * @return string
     */
    public function getTokenExpiresAt()
    {
        return $this->getData(self::TOKEN_EXPIRES_AT);
    }

    /**
     * Set expires time
     *
     * @param string $date
     * @return SubUser|void
     */
    public function setTokenExpiresAt($date)
    {
        return $this->setData(self::TOKEN_EXPIRES_AT, $date);
    }

    /**
     * Get is sent mail value
     *
     * @return bool
     */
    public function isSentMail()
    {
        return (bool) (int) $this->getData(self::IS_SENT_MAIL);
    }

    /**
     * Set is sent mail value
     *
     * @param int $value
     * @return SubUser|void
     */
    public function setIsSentMail($value)
    {
        return $this->setData(self::IS_SENT_MAIL, $value);
    }
}
