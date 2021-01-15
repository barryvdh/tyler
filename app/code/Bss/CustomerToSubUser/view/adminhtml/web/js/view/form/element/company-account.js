define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account',
    'Bss_CustomerToSubUser/js/service/RESTfulService'
], function (_, ko, Field, CompanyAccount, service) {
    'use strict';

    return Field.extend({
        defaults: {
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-account',
            companyAccount: {},
            wasAssigned: false,
            isNoCompanyAccountData: false,
            params: {},
            roleId: ko.observable(null),
            imports: {
                websiteId: '${ $.provider }:data.customer.website_id',
                customerEmail: '${ $.provider }:data.customer.email'
            },
            listens: {
                websiteId: 'whenChangeWebsite',
                'params.listCompanyAccounts': 'whenListCompanyAccountsCome'
            },
            exports: {
                roleId: 'customer_form.areas.assign_to_company_account.assign_to_company_account.company_account_roles:params.selectedRole'
            }
        },

        whenListCompanyAccountsCome: function (accounts) {
            if (accounts.length > 0) {
                this.initData();
            }
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super();

            return this;
        },

        initData: function () {
            var request, companyAccountData;

            try {
                request = service.getAssignedCompanyAccount(this.customerEmail, this.websiteId);
                request.done(function (companyAccountResponse) {
                    companyAccountData = this._getRowData(companyAccountResponse['company_customer'].id);

                    if (companyAccountData) {
                        CompanyAccount.data(companyAccountData);
                        CompanyAccount.roleUser(
                            {
                                'entity_id': companyAccountData['entity_id'],
                                'role_id': companyAccountResponse['sub_user']['related_role_id'],
                                'sub_id': companyAccountResponse['sub_user']['sub_id']
                            }
                        );
                    } else {
                        this.isNoCompanyAccountData(true);
                    }
                }.bind(this));
            } catch (e) {
                console.error(e);
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {this} Chainable.
         */
        initObservable: function () {
            this._super();

            this.observe('wasAssigned companyAccount roleId isNoCompanyAccountData');
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
            this.isNoCompanyAccountData(name === null);

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
        },

        /**
         * Get company account data from grid
         *
         * @param {Number} id
         * @returns {*}
         * @private
         */
        _getRowData: function (id) {
            return this.params.listCompanyAccounts.find(function (customer) {
                return customer[customer['id_field_name']] == id; //eslint-disable-line eqeqeq
            }.bind(this));
        },
    });
});
