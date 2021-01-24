define([
    'Bss_CustomerToSubUser/js/model/customer-protected-form-field',
    'Bss_CustomerToSubUser/js/action/custom-form-field',
    'moment'
], function (
    protectedFields,
    CustomerFormFieldsAction,
    moment
) {
    'use strict';

    var disabledCustomAttributeMixin = {
        defaults: {
            providerDataPath: '${ $.provider }:data.assign_to_company_account.',
            imports: {
                isSubUser: '${ $.provider }:data.assign_to_company_account.is_sub_user',
                companyAccountCustomAttributes:
                    '${ $.provider }:data.assign_to_company_account.company_account_custom_attributes'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains disabled the component if the the current customer is sub-user
         */
        initialize: function () {
            var attributeField;

            this._super();

            if (this.companyAccountCustomAttributes) {
                attributeField = this.companyAccountCustomAttributes.find(function (attribute) {
                    return this.index === attribute['attribute_code'];
                }.bind(this));
            }

            if (attributeField && !protectedFields.getNoneCopyFields().includes(attributeField['attribute_code'])
            ) {
                this.links.value = this.providerDataPath + attributeField['attribute_code'];
                // value = attributeField.value;
                //
                // if (this.formElement === 'date') {
                //     value = moment(attributeField.value).format(this.outputDateFormat);
                // }
                // this.initialValue = value;
                this.initLinks();
            }

            if (this.isSubUser && !protectedFields.getProtectedFields().includes(this.index)) {
                this.disabled(Boolean(this.isSubUser));
                // CustomerFormFieldsAction.addToggledComponentsValidation(
                //     {
                //         index: this.index,
                //         validation: this.validation,
                //         required: this.additionalClasses._required()
                //     }
                // );
                //this.validation = {};
                //this.additionalClasses._required(false);
            }

            return this;
        }
    };

    return function (target) {
        return target.extend(disabledCustomAttributeMixin);
    };
});
