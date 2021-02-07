define([
    'underscore',
    'jquery',
    'Magento_Ui/js/modal/modal-component',
    'mage/url',
    'Magento_Ui/js/modal/alert'
], function (_, $, Modal, urlBuilder, uiAlert) {
    'use strict';

    return Modal.extend({
        defaults: {
            responseData: null,
            links: {
                selectedProducts: 'product_listing.product_listing.product_columns.ids:selected'
            }
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Modal}
         */
        initObservable: function () {
            this._super();
            this.observe('responseData');

            return this;
        },

        /**
         * Prepare form data
         *
         * @return {Object} data
         * @private
         */
        _getFormData: function () {
            var data = this._getElementData(this.elems());

            return {
                'form_key': window.FORM_KEY,
                'order_restriction': data,
                'selected_products': this.selectedProducts
            };
        },

        /**
         * Get elements data
         *
         * @param {Object} elements
         * @private
         */
        _getElementData: function (elements) {
            var data = {},
                tmpData = {};

            elements.forEach(function (element) {
                if (element.elems && element.elems().length > 0) {
                    data = _.extend(data, this._getElementData(element.elems()));
                }

                if (element.value) {
                    tmpData[element.index] = null;

                    if (element.value() !== '') {
                        tmpData[element.index] = element.value();
                    }

                    data = _.extend(data, tmpData);
                }
            }, this);

            return data;
        },

        /**
         * Save the data
         */
        actionSave: function () {
            var postData, self = this;

            this.valid = 1;

            this.elems().forEach(this.validate, this);

            if (!this.valid) {
                return;
            }
            postData = this._getFormData();
            $('body').trigger('processStart');
            $.ajax({
                url: urlBuilder.build(this.saveUrl),
                type: 'POST',
                data: postData,
                dataType: 'json'
            }).done(function (response) {
                if (response.success) {
                    self.responseData(response);
                    self.closeModal();
                } else {
                    uiAlert({
                        content: response.message
                    });
                }
            }).fail(function (res) {
                console.error(res);
            }).always(function () {
                $('body').trigger('processStop');
            });
        }
    });
});
