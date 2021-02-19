define([
    'jquery',
    'fancybox/js/jquery.fancybox'
], function ($) {
    'use strict';

    return function (options, element) {
        if (options.href) {
            $(element).fancybox(options);
        }
    }
});
