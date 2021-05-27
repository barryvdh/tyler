/*
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 *  @category  BSS
 *  @package   Bss_AdminPreview
 *  @author    Extension Team
 *  @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 *  @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    "jquery",
    "Magento_Customer/js/customer-data",
    "domReady!"
], function ($, customerData) {
    return function () {
    	customerData.reload('cart');
    	customerData.reload('customer');
        setTimeout(function () {
            $('#loginascustomer-button').click();
        }, 5000);
    }
});