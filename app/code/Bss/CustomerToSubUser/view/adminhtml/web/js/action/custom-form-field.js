define([
    'underscore',
    'ko',
    'uiRegistry',
    'Bss_CustomerToSubUser/js/model/customer-protected-form-field',
    'Bss_CustomerToSubUser/js/model/company-account',
    'moment'
], function (_, ko, uiRegistry, protectedFields, CompanyAccount, moment) {
    'use strict';

    return {
        options: {
            customerFormFieldSelector: 'index = customer',
            customerInformationComponentsForToggle: [],
            customerInformationComponentsToggled: []
        },

        /**
         * Force ensure that the is company account attribute is '0'
         * if the current customer was assigned as company account
         *
         * @param {Boolean} disable
         */
        toggle: function (disable) {
            var fieldsForToggle;

            fieldsForToggle = this._getCustomerInformationFieldForToggle();

            _.each(fieldsForToggle, function (item) {
                try {
                    if (disable === true && item.disabled() === true) {
                        return;
                    }
                    // eslint-disable-next-line eqeqeq
                    if (disable === true && item.disabled() != disable) {
                        this.options.customerInformationComponentsToggled.push(item);
                    }

                    item.disabled(disable);
                    this._setCustomAttributeValuesFromCompanyAccount(item);
                } catch (e) {
                    console.error(e);
                }
            }.bind(this));
        },

        /**
         * Copy company account custom attributes to sub-user account in form
         *
         * @param {Object} item
         * @private
         */
        _setCustomAttributeValuesFromCompanyAccount: function (item) {
            var companyAccountCustomAttributes = CompanyAccount.data() ?
                CompanyAccount.data()['custom_attributes'] :
                undefined,
                attributeField,
                value;

            if (companyAccountCustomAttributes) {
                attributeField = companyAccountCustomAttributes.find(function (attribute) {
                    return item.index === attribute['attribute_code'];
                });

                if (attributeField && !protectedFields.getNoneCopyFields().includes(attributeField['attribute_code'])) {
                    value = attributeField.value;

                    if (item.formElement === 'date') { // eslint-disable-line max-depth
                        value = moment(attributeField.value).format(item.outputDateFormat);
                    }
                    item.value(value);
                }
            } else {
                item.value('0');
            }
        },

        /**
         * Get customer information field for toggle
         *
         * @returns {Array}
         * @private
         */
        _getCustomerInformationFieldForToggle: function () {
            var fieldsForToggle;

            if (this.options.customerInformationComponentsToggled.length) {
                fieldsForToggle = this.options.customerInformationComponentsToggled;
                this.options.customerInformationComponentsToggled = [];

                return fieldsForToggle;
            }

            if (!this.options.customerInformationComponentsForToggle.length) {
                this._filterElements(
                    uiRegistry.get(this.options.customerFormFieldSelector).elems()
                );
            }

            return this.options.customerInformationComponentsForToggle;
        },

        /**
         * Extract all fields form fieldsets
         *
         * @param {Array} elements
         * @private
         */
        _filterElements: function (elements) {
            if (!elements || !elements.length) {
                return;
            }

            _.each(elements, function (element) {
                if (this._isCollection(element)) {
                    this._filterElements(element.elems());

                    return;// continue
                }

                if (!protectedFields.getProtectedFields().includes(element.index) &&
                    ko.isObservable(element.disabled)
                ) {
                    this.options.customerInformationComponentsForToggle.push(element);
                }
            }.bind(this));
        },

        /**
         * Is component are collection
         *
         * @param {Object} element
         * @returns {Boolean}
         * @private
         */
        _isCollection: function (element) {
            return typeof element.initChildCount === 'number' && element.initChildCount !== 0;
        }
    };
});
