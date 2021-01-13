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
 * @package    Bss_BrandRepresentative
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/form/element/multiselect',
], function ($, url,  multiselect) {
    'use strict';

    return multiselect.extend({

        setCountryId: function (countryId) {
            let options = this.callAjaxProvinces(countryId);

            if (data.error) {
                return this;
            }

            this.options([]);
            this.setOption(options);
            this.set('newOption', options);
        },
        callAjaxProvinces: function (countryId) {
            $.ajax({
                type: "POST",
                dataType: "json",
                contentType: "application/json",
                url: url.build('namespace_module/controllername/action/\''),
                success: function (result) {
                    $('#result').html(result);
                },
                error: function (error) {
                    $('#result').html(error);
                }
            });
        },
    });
});
