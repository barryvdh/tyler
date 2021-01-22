define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    var changeAddToCartButtonTextAdded = {
        options: {
            addToCartButtonTextAddedFailed: $t('Not allowed'),
            fallbackAddedText: null,
            confirmMessage: "Please, confirm modal closing."
        },

        /**
         * Invoke super initialize and module initialize logic
         *
         * @private
         */
        _create: function () {
            this.fallbackAddedText = this.addToCartButtonTextAdded;
            this._bindCheckTheRestrictionAddToCartRes();
            this._super();
        },

        /**
         * Bind to listen the add to cart event for check if this action be restrict
         *
         * @private
         */
        _bindCheckTheRestrictionAddToCartRes: function () {
            $(document).on('ajax:addToCart', function (e, data) {
                if (data.response && data.response['bss_is_restricted'] === true) {
                    this.options.addToCartButtonTextAdded = this.options.addToCartButtonTextAddedFailed;
                } else {
                    this.options.addToCartButtonTextAdded = this.options.fallbackAddedText;
                }
            }.bind(this));
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, changeAddToCartButtonTextAdded);

        return $.mage.catalogAddToCart;
    };
});
