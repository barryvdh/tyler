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
        'wysiwygAdapter'
    ],
    function ($) {
        return function (config) {
            var urlTypeTemplate= config.urlTypeTemplate;
            $("#popup_load_template").click(
                function (e) {
                    try {
                        tinyMCE.get('popup_popup_content').setContent("")
                    } catch (e) {
                        $("#togglepopup_popup_content").click();
                    }
                    var typeTemplate = $("#popup_type_template").val();
                    $.ajax(
                        {
                            url: urlTypeTemplate,
                            type: "post",
                            dataType: "json",
                            data: {
                                type_template: typeTemplate
                            },
                            success: function (data) {
                                try {
                                    tinyMCE.get('popup_popup_content').setContent(data)
                                } catch (e) {

                                }
                            }
                        }
                    );
                }
            );
        }
    }
);
