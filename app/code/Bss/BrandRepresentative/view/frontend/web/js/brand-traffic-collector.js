define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('bss.brandTrafficCollect', {
        /**
         * Initilization widget
         *
         * @private
         */
        _create: function () {
            try {
                this.storeNewVisit();
            } catch (e) {
                console.error(e);
            }
        },

        /**
         * Execute add n
         * @returns {*}
         */
        storeNewVisit: function () {
            if (this.options.addNewVisitUrl) {
                return $.ajax({
                    url: this.options.addNewVisitUrl,
                    data: {
                        'form_key': $.cookie('form_key')
                    },
                    dataType: 'json',
                    method: 'POST'
                });
            }
        }
    });

    return $.bss.brandTrafficCollect;
});
