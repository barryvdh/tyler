<?php
/**
 * Password Protected
 *
 * @category    Addify
 * @package     Addify_PasswordProtected
 * @author      Addify
 * @Email       addifypro@gmail.com
 *
 */
namespace Addify\PasswordProtected\Model;

    class Passwords extends \Magento\Framework\Model\AbstractModel
    {   
        protected function _construct()
        {
            $this->_init('Addify\PasswordProtected\Model\ResourceModel\Passwords');
        }
	}