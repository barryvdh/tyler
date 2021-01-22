define([
    'Magento_Ui/js/form/element/single-checkbox',
    'Bss_CustomerToSubUser/js/action/is-company-account-field'
], function (Switcher, CustomerInformationFormFieldsAction) {
    'use strict';

    return Switcher.extend({
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
            this._super();

            // if (this.isSubUser) {
            //     console.log(Boolean(this.isSubUser));
            //     CustomerInformationFormFieldsAction.toggle(Boolean(this.isSubUser));
            //     // this.disabled(Boolean(this.isSubUser));
            //     // this.value('0');
            // }

            return this;
        }
    });
});
