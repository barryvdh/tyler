define([
    'jquery'
], function ($) {
    'use strict';

    return function (config, element) {
        $(document).ready(function () {
            var url = new URL(window.location);

            if (url.searchParams.get('p') ||
                url.searchParams.get('product_list_order') ||
                url.searchParams.get('product_list_dir')
            ) {
                $('html, body').scrollTop($(element).offset().top);
            }
        });
    };
});
