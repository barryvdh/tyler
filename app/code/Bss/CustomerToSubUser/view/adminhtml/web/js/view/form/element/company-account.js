define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account'
], function (_, ko, Field, CompanyAccount) {
    'use strict';

    return Field.extend({
        defaults: {
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-account',
            companyAccount: {},
            wasAssigned: false,
            params: {},
            imports: {
                websiteId: '${ $.provider }:data.customer.website_id'
            },
            listens: {
                websiteId: 'whenChangeWebsite'
            }
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
         * When select other website
         * Clear the selected company account
         *
         * @param {Number} id
         */
        whenChangeWebsite: function (id) {
            // eslint-disable-next-line eqeqeq
            if (CompanyAccount.data() && CompanyAccount.data()['website_id'] != id) {
                CompanyAccount.data(null);
            }
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
            var name = null,
                tmpCompanyData = {};

            if (data) {
                tmpCompanyData = data;
                name = data.name;
                this.value(data['entity_id']);
            }

            this.companyAccount(tmpCompanyData);
            this.wasAssigned(name !== null);

            return this;
        },

        /**
         * Retrieves group label associated with a provided group id.
         *
         * @returns {String|null}
         */
        getGroupName: function () {
            return ko.pureComputed(function () {
                if (this.companyAccount()['group_id']) {
                    return this.getLabelFromOptions(
                        this.params.groupOptions,
                        this.companyAccount()['group_id']
                    );
                }

                return null;
            }, this);
        },

        /**
         * Get the website name from website ids
         *
         * @returns {String|null}
         */
        getWebsiteName: function () {
            return ko.pureComputed(function () {
                if (this.companyAccount()['website_id']) {
                    return this.getLabelFromOptions(
                        this.params.websiteOptions,
                        this.companyAccount()['website_id']
                    );
                }

                return null;
            }, this);
        },

        /**
         * Get label from options
         *
         * @param {Array} options
         * @param {Array} values
         * @returns {String}
         */
        getLabelFromOptions: function (options, values) {
            var label = [];

            options.forEach(function (item) {
                if (_.contains(values, item.value + '')) {
                    label.push(item.label);
                }
            });

            return label.join(', ');
        }
    });
});
