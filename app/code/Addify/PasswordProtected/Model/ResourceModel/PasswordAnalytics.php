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
namespace Addify\PasswordProtected\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PasswordAnalytics extends AbstractDb{

    protected function _construct()
    {
        $this->_init('addify_passwords_analytics', 'pa_id');

    } 

}

