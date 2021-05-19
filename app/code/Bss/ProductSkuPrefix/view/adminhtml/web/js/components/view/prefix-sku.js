define([
    'ko',
    'Magento_Catalog/js/components/import-handler'
], function (ko, Sku) {
    'use strict';

    return Sku.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (!this.usePrefix) {
                return this;
            }

            if (this.hasOwnProperty('editable')) {
                // disable required validation for editable prefix
                // to allowed admin leave blank to auto generated sku
                if (this.editable) {
                    this._setValue('required', false);
                    this.validation['required-entry'] = false;
                }
                this._setValue('disabled', !Boolean(this.editable))
            }
        },

        /**
         * Set local variable
         *
         * @param {String} prop
         * @param {*} value
         * @private
         */
        _setValue: function (prop, value) {
            if (ko.isObservable(this[prop])) {
                this[prop](value);
            } else {
                this[prop] = value;
            }
        },

        /**
         * Disable core function if the prefix sku be config
         *
         * @returns {*}
         */
        setHandlers: function () {
            if (!this.usePrefix) {
                return this._super();
            }
        }
    });
});
