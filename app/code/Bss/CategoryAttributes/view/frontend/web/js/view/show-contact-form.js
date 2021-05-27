define([
    'jquery',
    'fancybox/js/jquery.fancybox'
], function ($) {
    'use strict';

    return function (options, element) {
        if (!$.isEmptyObject(options)) {
            $(element).fancybox(options);
        }
    };
});
