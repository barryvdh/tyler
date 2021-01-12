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
namespace Bss\CompanyAccount\Api\Data;

/**
 * Interface SubUserInterface
 *
 * @package Bss\CompanyAccount\Api\Data
 */
interface SubUserInterface
{
    /**
     * Constants for keys of data array.
     */
    const ID = 'sub_id';
    const CUSTOMER_ID = 'customer_id';
    const NAME = 'sub_name';
    const EMAIL = 'sub_email';
    const PASSWORD = 'sub_password';
    const STATUS = 'sub_status';
    const TOKEN = 'token';
    const ROLE_ID = 'role_id';
    const CREATE_AT = 'created_at';
    const QUOTE_ID = 'quote_id';
    const PARENT_QUOTE_ID = 'parent_quote_id';
    const TOKEN_EXPIRES_AT = 'token_expires_at';
    const IS_SENT_MAIL = 'is_sent_email';

    /**
     * Get token expires time
     *
     * @return string
     */
    public function getTokenExpiresAt();

    /**
     * Set expires time
     *
     * @param string $date
     * @return void
     */
    public function setTokenExpiresAt($date);

    /**
     * Get quote id
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Set quote Id
     *
     * @param int $id
     * @return void
     */
    public function setQuoteId(int $id);

    /**
     * Get parent quote id
     *
     * @return int
     */
    public function getParentQuoteId();

    /**
     * Set parent quote id
     *
     * @param int $id
     * @return void
     */
    public function setParentQuoteId(int $id);

    /**
     * Get create time
     *
     * @return string
     */
    public function getCreateTime();

    /**
     * Set create time
     *
     * @param string $time
     * @return void
     */
    public function setCreateTime(string $time);

    /**
     * Get sub user id
     *
     * @return int
     */
    public function getSubUserId();

    /**
     * Set sub user id
     *
     * @param int $id
     * @return void
     */
    public function setSubUserId($id);

    /**
     * Get company customer related id
     *
     * @return int
     */
    public function getCompanyCustomerId();

    /**
     * Associate company customer
     *
     * @param int $id
     * @return void
     */
    public function setCompanyCustomerId($id);

    /**
     * Get sub user name
     *
     * @return string
     */
    public function getSubUserName();

    /**
     * Set name for sub user
     *
     * @param string $name
     * @return void
     */
    public function setSubUserName(string $name);

    /**
     * Get sub user email
     *
     * @return string
     */
    public function getSubUserEmail();

    /**
     * Set email for sub user
     *
     * @param string $email
     * @return void
     */
    public function setSubUserEmail(string $email);

    /**
     * Get sub user hashed password
     *
     * @return string
     */
    public function getSubUserPassword();

    /**
     * Set sub user password
     *
     * @param string $password
     * @return void
     */
    public function setSubUserPassword(string $password);

    /**
     * Get status of sub user
     *
     * @return int
     */
    public function getSubUserStatus();

    /**
     * Set sub user status
     *
     * @param int $status
     * @return void
     */
    public function setSubUserStatus(int $status);

    /**
     * Get token
     *
     * @return string
     */
    public function getSubUserToken();

    /**
     * Set token
     *
     * @param string $token
     * @return void
     */
    public function setSubUserToken($token);

    /**
     * Get related role id
     *
     * @return int
     */
    public function getRelatedRoleId();

    /**
     * Associate role for sub user
     *
     * @param int $id
     * @return void
     */
    public function setRoleId(int $id);

    /**
     * Get is sent mail field value
     *
     * @return bool
     */
    public function isSentMail();

    /**
     * Set is sent mail value
     *
     * @param int $value
     * @return void
     */
    public function setIsSentMail($value);
}
