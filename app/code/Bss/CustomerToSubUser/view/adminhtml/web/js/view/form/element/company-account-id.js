define([
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account'
], function (ko, Field, CompanyAccount) {
    'use strict';

    return Field.extend({
        defaults: {
            companyAccount: {},
            wasAssigned: false
        },

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

            this.observe('companyAccountName wasAssigned companyAccount');
            CompanyAccount.data.subscribe(this.whenCompanyAccountUpdate, this);
            this.value.subscribe(this.whenValueUpdate, this);

            return this;
        },

        /**
         * If value was empty, then clear the local data
         *
         * @param {String} value
         */
        whenValueUpdate: function (value) {
            if (!value) {
                CompanyAccount.data(null);
            }
        },

        /**
         * When company account was selected
         *
         * @param {Object} data
         */
        whenCompanyAccountUpdate: function (data) {
            console.log(data);
            var name = null, tmpCompanyData = {};

            if (data) {
                tmpCompanyData = data;
                name = data.name;
                this.value(data['entity_id']);
            }

            this.companyAccount(tmpCompanyData);
            this.wasAssigned(name !== null);

            return this;
        }
    });
});
