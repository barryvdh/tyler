define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Bss_CustomerToSubUser/js/model/company-account'
], function ($, ko, Select, CompanyAccount) {
    'use strict';

    return Select.extend({
        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            this.whenSelectCompanyAccount(null);

            return this;
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {*}
         */
        initObservable: function () {
            this._super();
            CompanyAccount.data.subscribe(this.whenSelectCompanyAccount, this);

            return this;
        },

        /**
         * Update the visibility of component
         *
         * @param {Object|null} data
         */
        whenSelectCompanyAccount: function (data) {
            if (data && data.hasOwnProperty('entity_id')) {
                this.getCompanyAccountRoles(data['entity_id']);
                this.visible(true);
            } else {
                this.visible(false);
            }
        },

        getCompanyAccountRoles: function (id) {
            var request;

            try {
                request = $.ajax({

                });
            } catch (e) {
                console.error(e);
            }
        }
    });
});
