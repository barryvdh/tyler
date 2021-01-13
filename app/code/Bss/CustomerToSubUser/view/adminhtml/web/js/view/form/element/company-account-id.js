define([
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account'
], function (ko, Field, CompanyAccount) {
    'use strict';

    return Field.extend({
        /**
         * The customer was assigned to company account
         *
         * @return {Boolean}
         */
        wasAssigned: ko.computed(function () {
            var companyAccount = CompanyAccount.data();

            if (companyAccount) {
                companyAccount.hasOwnProperty('entity_id')
            }

            return false;
        }),

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            console.log(this.elementTmpl);
            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {this} Chainable.
         */
        initObservable: function () {
            this._super();

            return this;
        },

        /**
         * Get company account was assigned
         *
         * @returns {Object|null}
         */
        getCompanyAccount: function () {
            return CompanyAccount.data();
        },

        /**
         * Get company account name
         *
         * @returns {string}
         */
        getCompanyAccountName: function () {
            return ko.computed(function () {
                if (this.wasAssigned()) {
                    return this.getCompanyAccount()['firstname'] + this.getCompanyAccount()['lastname'];
                }
            }, this, { pure: true })
        }
    });
});
