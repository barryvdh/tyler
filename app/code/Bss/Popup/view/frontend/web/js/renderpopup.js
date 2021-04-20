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
 * @package    Bss_Popup
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
        'jquery'
    ],
    function ($) {
        return function (config) {
            $(document).ready(function () {

                var page = config.page;
                var storeViewID = config.storeViewId;
                var productId = config.productId;
                var categoryId = config.categoryId;
                var targetDiv = ".popup_container";
                var pageInformation = config.pageInformation;
                if ($('.popup_container').length === 0) {
                    $('header').after('<div class="popup_container"></div>');
                }
                $.ajax({
                    url: config.updateUrl,
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        pageDisplay: page,
                        storeViewID: storeViewID,
                        categoryId: categoryId,
                        productId: productId,
                        pageInformation: pageInformation
                    },
                    success: function (res) {
                        $(targetDiv).append(res);
                        $(".popup_container").trigger('contentUpdated');
                    }
                });

            })
        }
    }
);