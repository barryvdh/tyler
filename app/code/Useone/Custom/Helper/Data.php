<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Useone\Custom\Helper;

use Magento\Framework\Registry;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $session;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $session
    ) {
        $this->_customerSession = $session;
        parent::__construct($context);
    }
    public function getCustomerStatus(){
        return $this->_customerSession->isLoggedIn();
    }
}
