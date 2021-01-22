define([
    'Bss_CustomerToSubUser/js/model/customer-protected-form-field',
    'Bss_CustomerToSubUser/js/action/is-company-account-field'
], function (protectedFields, CustomerFormFieldsAction) {
    'use strict';

    var disabledCustomAttributeMixin = {
        defaults: {
            imports: {
                isSubUser: '${ $.provider }:data.assign_to_company_account.sub_id'
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains disabled the component if the the current customer is sub-user
         */
        initialize: function () {
            var disabledComponent;

            this._super();

            if (this.index == 'ca_test_dropdown' || this.index == 'is_company_account') {
                console.log(this.isSubUser);
            }
            if (this.isSubUser && !protectedFields.fields.includes(this.index)) {
                this.disabled(Boolean(this.isSubUser));
                CustomerFormFieldsAction.addToggledComponentsValidation({index: this.index, validation: this.validation});
                this.validation = {};
                this.value('');
                this.additionalClasses._required(false);
            }

            return this;
        }
    };

    return function (target) {
        return target.extend(disabledCustomAttributeMixin);
    };
});
