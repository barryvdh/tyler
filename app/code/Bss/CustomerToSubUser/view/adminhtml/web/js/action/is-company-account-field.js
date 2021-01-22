define([
    'underscore',
    'ko',
    'uiRegistry'
], function (_, ko, uiRegistry) {
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
                tmpValidation;

            fieldsForToggle = this._getCustomerInformationFieldForToggle();

            _.each(fieldsForToggle, function (item) {
                try {
                    // eslint-disable-next-line eqeqeq
                    if (disable === true && item.disabled() != disable) {
                        this.options.customerInformationComponentsToggled.push(item);
                        this.options.toggledComponentsValidation.push({index: item.index, validation: item.validation});
                    }

                    item.disabled(disable);

                    fieldValidationIndex = this.options.toggledComponentsValidation.map(function(component) {
                        return component.index;
                    }).indexOf(item.index);

                    tmpValidation = {};
                    if (fieldValidationIndex != -1) {
                        tmpValidation = this.options.toggledComponentsValidation[fieldValidationIndex].validation;
                    }
                    item.validation = tmpValidation;
                    this.options.toggledComponentsValidation.splice(fieldValidationIndex, 1);

                    item.additionalClasses._required(!disable);
                } catch (e) {
                    console.error(e);
                }
            }.bind(this));

            console.log(this.options.customerInformationComponentsToggled);
            console.log(this.options.toggledComponentsValidation);
            // if (isCompanyAccountSwitcherComponent) {
            //     console.log('change state of component: ' + disable);
            //     isCompanyAccountSwitcherComponent.disabled(disable);
            //     isCompanyAccountSwitcherComponent.value('0');
            // }
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

                if (!this.options.protectedFields.includes(element.index)
                    && ko.isObservable(element.disabled)
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
