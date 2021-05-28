define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.BssProductGridInlineEditor', widget, {
            options: {
                prefixSkuData: null
            },

            /**
             * Is allow product type
             *
             * @param {object} attr
             * @param {string} product_type
             * @param {string} column_name
             * @returns {boolean|*}
             */
            isAllowInputEditwithTypeProduct: function (attr, product_type, column_name) {
                var prefixSku;

                if (column_name !== 'sku') {
                    return this._super(attr, product_type, column_name);
                }

                prefixSku = this.getPrefixData(product_type);

                if (prefixSku && prefixSku[1] && prefixSku[1]['editable'] === undefined) {
                    return false;
                }

                return this._super(attr, product_type, column_name);
            },

            /**
             * Get prefix data
             *
             * @param {string} type
             * @returns {[string, any]}
             */
            getPrefixData: function (type) {
                var prefixSku;

                if (typeof this.options.prefixSkuData === 'object') {
                    prefixSku = Object.entries(this.options.prefixSkuData).find(function (value) {
                        if (value[1]) {
                            return value[1]['product_type'] === type;
                        }
                    });
                }

                return prefixSku;
            }
        });
        return $.mage.BssProductGridInlineEditor;
    };
});
