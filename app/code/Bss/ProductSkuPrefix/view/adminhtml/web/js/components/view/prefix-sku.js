define([
    'underscore',
    'ko',
    'Magento_Catalog/js/components/import-handler'
], function (_, ko, Sku) {
    'use strict';

    const modifyFields = ['value', 'required', 'validation', 'disabled', 'notice', 'allowImport'];

    return Sku.extend({
        defaults: {
            defaultCfData: {},
            rollBackData: {},
            usePrefix: false,
            imports: {
                isDownloadable: '${ $.ns }.${ $.ns }.downloadable.is_downloadable:value',
                hasWeight: '${ $.ns }.${ $.ns }.product-details.container_weight.product_has_weight:value'
            }
        },
        initObservable: function () {
            this._super();

            this.observe('usePrefix isDownloadable prefixData hasWeight');
            this.isDownloadable.subscribe(this.isDownloadableProduct, this);
            this.hasWeight.subscribe(this.onHasWeight, this);

            return this;
        },

        /**
         * On has weight change
         *
         * @param {String} newValue
         */
        onHasWeight: function (newValue) {
            var prefixData;

            if (this.productType === 'downloadable' && parseInt(newValue) === 1) {
                prefixData = this._getPrefixData('simple');
                if (prefixData) {
                    this._storeRollbackData();
                    // set simple prefix
                    this._setPrefixSku(prefixData);
                    return;
                }

                // reset to default
                this._rollback(true);
                return;
            }

            // still downloadable product
            if (this.productType === 'downloadable' &&
                parseInt(newValue) === 0
            ) {
                prefixData = this._getPrefixData('downloadable');

                if (prefixData) {
                    this._storeRollbackData();
                    // set simple prefix
                    this._setPrefixSku(prefixData);
                    this._setValue('value', Boolean(this.productSku) ? this.productSku : "");
                    return;
                }
            }

            if (this.productType !== 'downloadable' &&
                parseInt(newValue) === 0 &&
                parseInt(this.isDownloadable()) == 0
            ) {
                prefixData = this._getPrefixData('virtual');

                if (prefixData) {
                    this._storeRollbackData();
                    // set simple prefix
                    this._setPrefixSku(prefixData);
                    return;
                }
            }

            if (this.productType !== 'downloadable' &&
                parseInt(newValue) === 0 &&
                parseInt(this.isDownloadable()) == 1
            ) {
                prefixData = this._getPrefixData('downloadable');

                if (prefixData) {
                    this._storeRollbackData();
                    // set simple prefix
                    this._setPrefixSku(prefixData);
                    return;
                }
            }

            this._rollback(true);
        },

        /**
         * When is downloadable checked
         *
         * @param {String} checked
         */
        isDownloadableProduct: function (checked) {
            var prefixData;

            if (parseInt(checked) === 1) {
                this._storeRollbackData();
                prefixData = this._getPrefixData('downloadable');

                if (prefixData) {
                    this._setPrefixSku(prefixData);

                    if ((this.productType === 'downloadable' || this.productType === 'virtual') &&
                        Boolean(this.productSku)
                    ) {
                        this._setValue('value', this.productSku);
                    } else {
                        this._setValue('value', "");
                    }
                    return;
                }

                return;
            }

            this._rollback();
        },

        /**
         * Set prefix sku
         *
         * @param {Object} prefixData
         * @private
         */
        _setPrefixSku: function (prefixData) {
            if (prefixData.hasOwnProperty('editable')) {
                this._setValue('notice', this.prefixNotice);
            } else {
                this._setValue('disabled', true);
            }

            this._setValue('allowImport', false);
            this._setValue('required', false);
            this.validation['required-entry'] = false;
            if (this._getValue('value') !== this.productSku) {
                this._setValue('value', "");
            }
        },

        _storeRollbackData: function () {
            var noBackupDefault = true;
            if (!_.isEmpty(this.defaultCfData)) {
                noBackupDefault = false;
            }
            modifyFields.forEach(function (field) {
                if (noBackupDefault) {
                    if (field === 'validation') {
                        this.defaultCfData[field] = {'required-entry': this._getValue(field)};
                    } else {
                        this.defaultCfData[field] = this._getValue(field);
                    }
                }
                this.rollBackData[field] = this._getValue(field);
            }, this);
        },

        _rollback: function (toDefault = false) {
            if (toDefault && !_.isEmpty(this.defaultCfData)) {
                modifyFields.forEach(function (field) {
                    this._setValue(field, this.defaultCfData[field]);
                }, this);

                return;
            }

            if (!_.isEmpty(this.rollBackData)) {
                modifyFields.forEach(function (field) {
                    this._setValue(field, this.rollBackData[field]);
                }, this);

                this.rollBackData = {};
            }
        },

        /**
         * Get config prefix data
         *
         * @param {String} productType
         * @private
         */
        _getPrefixData(productType) {
            return Object.values(this.prefixData()).find(function (data) {
                return data['product_type'] === productType;
            });
        },

        /** @inheritdoc */
        initialize: function () {
            var prefixData;

            this._super();
            prefixData = this._getPrefixData(this.productType);

            if (!prefixData) {
                return this;
            }

            this._setPrefixSku(prefixData);

            return this;
        },

        /**
         * Set local variable
         *
         * @param {String} prop
         * @param {*} value
         * @private
         */
        _setValue: function (prop, value) {
            if (prop === 'validation') {
                if (ko.isObservable(this[prop]['required-entry'])) {
                    this[prop]['required-entry'](value['required-entry']);
                } else {
                    this[prop]['required-entry'] = value['required-entry'];
                }

                return;
            }

            if (ko.isObservable(this[prop])) {
                this[prop](value);
            } else {
                this[prop] = value;
            }
        },

        /**
         * Get local variable
         *
         * @param {String} prop
         * @private
         */
        _getValue: function (prop) {
            if (prop === 'validation') {
                if (ko.isObservable(this[prop]['required-entry'])) {
                    this[prop]['required-entry']();
                }

                return this[prop]['required-entry'];
            }

            if (ko.isObservable(this[prop])) {
                return this[prop]();
            }

            return this[prop];
        },

        /**
         * Disable core function if the prefix sku be config
         *
         * @returns {*}
         */
        setHandlers: function () {
            var prefixData = this._getPrefixData(this.productType);

            if (!prefixData) {
                return this._super();
            }
        }
    });
});
