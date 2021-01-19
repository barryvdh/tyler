define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account',
    'Bss_CustomerToSubUser/js/service/RESTfulService',
    'Bss_CustomerToSubUser/js/action/is-company-account-field'
], function (
    _,
    ko,
    Field,
    CompanyAccount,
    service,
    isCompanyAccountField
) {
    'use strict';

    return Field.extend({
        defaults: {
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-account',
            companyAccount: {},
            wasAssigned: false,
            isNoCompanyAccountData: false,
            params: {},
            imports: {
                websiteId: '${ $.provider }:data.customer.website_id',
                customerEmail: '${ $.provider }:data.customer.email'
            },
            listens: {
                websiteId: 'whenChangeWebsite',
                'params.listCompanyAccounts': 'whenListCompanyAccountsCome'
            }
        },

        /**
         * After load then list of company account, fetch the assigned company account data
         *
         * @param {Array} accounts
         */
        whenListCompanyAccountsCome: function (accounts) {
            if (accounts.length > 0) {
                this.initData();
            } else {
                this.isNoCompanyAccountData(true);
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

        /**
         * Fetch the sub-user and company account data
         */
        initData: function () {
            var request, companyAccountData;

            try {
                request = service.getAssignedCompanyAccount(this.customerEmail, this.websiteId);
                request.done(function (companyAccountResponse) {
                    companyAccountData = this._getRowData(companyAccountResponse['company_customer'].id);

                    if (companyAccountData) {
                        console.log('company account update in side (init data): ' + this.value());
                        CompanyAccount.data(companyAccountData);
                        CompanyAccount.roleUser(
                            {
                                'entity_id': companyAccountData['entity_id'],
                                'role_id': companyAccountResponse['sub_user']['related_role_id'],
                                'sub_id': companyAccountResponse['sub_user']['sub_user_id']
                            }
                        );
                        this._setRole();
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

            this.observe('wasAssigned companyAccount roleId isNoCompanyAccountData subId');
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
                console.log('company account update in side (website change): ' + this.value());
                CompanyAccount.data(null);
            }
        },

        /**
         * If value was empty, then clear the local data
         *
         * @param {String|Number|Object} value
         */
        whenValueUpdate: function (value) {
            var companyAccountData = null;

            if (value) {
                companyAccountData = this._getRowData(value);
            }

            console.log('company account update in side (value change): ' + this.value());
            CompanyAccount.data(companyAccountData);
            this._setRole();
        },

        /**
         * When company account was selected
         *
         * @param {Object} data
         */
        whenCompanyAccountUpdate: function (data) {
            var companyIdData = '',
                tmpCompanyData = {};

            if (data) {
                tmpCompanyData = data;
                companyIdData = Number(data['entity_id']);
            }

            this.companyAccount(tmpCompanyData);
            this.wasAssigned(Boolean(companyIdData));
            this.isNoCompanyAccountData(!companyIdData);

            console.log('Subscriber: company account update in side listener: ' + this.value());
            this.value(companyIdData);

            // Force ensure that the is company account attribute is '0'
            // if the current customer was assigned as company account
            isCompanyAccountField.toggle(Boolean(companyIdData));

            return this;
        },

        /**
         * Set selected role to role component
         *
         * @private
         */
        _setRole: function () {
            CompanyAccount.roleId.valueHasMutated();
        },

        /**
         * Get company account data from grid
         *
         * @param {Number} id
         * @returns {*}
         * @private
         */
        _getRowData: function (id) {
            if (this.params.listCompanyAccounts) {
                return this.params.listCompanyAccounts.find(function (customer) {
                    return customer[customer['id_field_name']] == id; //eslint-disable-line eqeqeq
                });
            }

            return null;
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
