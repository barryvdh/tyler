define([
    'ko',
    'Magento_Catalog/js/components/import-handler'
], function (ko, Sku) {
    'use strict';

    return Sku.extend({
        defaults: {},

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (!this.prefixSku) {
                return this;
            }
            // set prefix sku
            if (ko.isObservable(this.value)) {
                this.value(this.prefixSku);
            } else {
                this.value = this.prefixSku;
            }
        },

        /**
         * Disable core function if the prefix sku be config
         *
         * @returns {*}
         */
        setHandlers: function () {
            if (!this.prefixSku) {
                return this._super();
            }
        }
    });
});
