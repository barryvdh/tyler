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
            isCompanyAccountAttributeSelector: 'index = bss_is_company_account',
            customerFormFieldSelector: 'index = customer',
            customerInformationComponentsForToggle: [],
            customerInformationComponentsToggled: [],
            protectedFields: [
                'website_id',
                'group_id',
                'disable_auto_group_change',
                'container_group',
                'prefix',
                'firstname',
                'middlename',
                'lastname',
                'suffix',
                'email',
                'dob',
                'taxvat',
                'gender',
                'sendemail_store_id',
                'vertex_customer_code',
                'b2b_activasion_status'
            ],
            toggledComponentsValidation: []
        },

        /**
         * Force ensure that the is company account attribute is '0'
         * if the current customer was assigned as company account
         *
         * @param {Boolean} disable
         */
        toggle: function (disable) {
            var isCompanyAccountSwitcherComponent = uiRegistry.get(this.options.isCompanyAccountAttributeSelector),
                fieldsForToggle,
                fieldValidationIndex,
                tmpValidation,
                requiredCss;

            fieldsForToggle = this._getCustomerInformationFieldForToggle();

            _.each(fieldsForToggle, function (item) {
                try {
                    if (disable === true && item.disabled() === true) {
                        return;
                    }
                    // eslint-disable-next-line eqeqeq
                    if (disable === true && item.disabled() != disable) {
                        this.options.customerInformationComponentsToggled.push(item);
                        // this.options.toggledComponentsValidation.push(
                        //     {
                        //         index: item.index,
                        //         validation: item.validation,
                        //         required: item.additionalClasses._required()
                        //     }
                        // );
                    }

                    item.disabled(disable);

                    // fieldValidationIndex = this.options.toggledComponentsValidation
                    //     .findIndex(function (component) {
                    //         return component.index === item.index;
                    //     });

                    //tmpValidation = {};
                    //requiredCss = false;

                    // if (fieldValidationIndex !== -1 && disable === false) {
                    //     tmpValidation = this.options.toggledComponentsValidation[fieldValidationIndex].validation;
                    //     requiredCss = this.options.toggledComponentsValidation[fieldValidationIndex].required;
                    // }

                    // console.log(requiredCss);
                    // item.validation = tmpValidation;
                    // item.additionalClasses._required(requiredCss);
                    // this.options.toggledComponentsValidation.splice(fieldValidationIndex, 1);

                    this._setCustomAttributeValuesFromCompanyAccount(item);
                } catch (e) {
                    console.error(e);
                }
            }.bind(this));
            // if (isCompanyAccountSwitcherComponent) {
            //     console.log('change state of component: ' + disable);
            //     isCompanyAccountSwitcherComponent.disabled(disable);
            //     isCompanyAccountSwitcherComponent.value('0');
            // }
        },

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
                    // item.initialValue = attributeField.value;
                    value = attributeField.value;

                    if (item.formElement === 'date') {
                        value = moment(attributeField.value).format(item.outputDateFormat);
                    }
                    // item.initialValue = value;
                    item.value(value);
                }
            } else {
                item.value('0');
            }

            console.log(item.value());
        },

        /**
         * Add disabled component validation
         * @param {Object} component
         */
        addToggledComponentsValidation: function (component) {
            this.options.toggledComponentsValidation.push(component);
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
