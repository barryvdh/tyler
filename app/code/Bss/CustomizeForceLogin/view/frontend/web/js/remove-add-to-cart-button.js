define([
    'jquery'
], function ($) {
    'use strict';

    return function (config) {

        if (config.removeAddToCartBtn) {
            $(document).ajaxComplete(function () {
                $(document).find("[data-role='tocart-form']").remove();
            });
            $("[data-role='tocart-form']").remove();
        }
    }
});
