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
 * @category  BSS
 * @package   Bss_Popup
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(
    [
        "jquery",
        'mage/url'
    ],
    function ($,urlBuilder) {
        $("#age_verification_yes").click(
            function (e) {
                $.magnificPopup.close();
            }
        );
        $("#age_verification_no").click(
            function (e) {
                var x=$("#age_verification_redirect").val();
                window.location.href = urlBuilder.build($("#age_verification_redirect").val());
            }
        );
    }
);
