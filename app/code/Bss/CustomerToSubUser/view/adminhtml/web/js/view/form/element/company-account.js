define([
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Bss_CustomerToSubUser/js/model/company-account',
    'Bss_CustomerToSubUser/js/service/RESTfulService',
    'uiRegistry'
], function (
    _,
    ko,
    Field,
    CompanyAccount,
    service,
    uiRegistry
) {
    'use strict';

    return Field.extend({
        defaults: {
            isCompanyAccountAttributeSelector: 'index = bss_is_company_account',
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-account',
            companyAccount: {},
            wasAssigned: false,
            isNoCompanyAccountData: false,
            params: {},
            subId: null,
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
                                'sub_id': companyAccountResponse['sub_user']['sub_user_id']
                            }
                        );
                        this.subId(companyAccountResponse['sub_user']['sub_user_id']);
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
            CompanyAccount.roleUser.subscribe(function (data) {
                var oldValue = JSON.parse(this.value()), subId;

                if (data && data['sub_id']) {
                    subId = {
                        'sub_id': data['sub_id']
                    };

                    this.value(
                        JSON.stringify({...oldValue, ...subId})
                    );
                }
            }, this);

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
            var companyIdData = null,
                tmpCompanyData = {},
                isCompanyAccountSwitcherComponent;

            if (data) {
                tmpCompanyData = data;
                companyIdData = {
                    'company_account_id': data['entity_id']
                };

                if (CompanyAccount.roleUser() &&
                    CompanyAccount.roleUser()['sub_id']
                ) {
                    companyIdData = {
                        ...companyIdData,
                        ...{
                            'sub_id': CompanyAccount.roleUser()['sub_id']
                        }
                    }
                }
            }

            this.companyAccount(tmpCompanyData);
            this.wasAssigned(companyIdData !== null);
            this.isNoCompanyAccountData(companyIdData === null);
            this.value(companyIdData !== null ? JSON.stringify(companyIdData) : '');

            // Set fieldset is not changed if company account data is not selected
            this._resetFieldset(this, companyIdData !== null);

            // Force ensure that the is company account attribute is '0' if the current customer was assigned as company account
            isCompanyAccountSwitcherComponent = uiRegistry.get(this.isCompanyAccountAttributeSelector);

            if (isCompanyAccountSwitcherComponent) {
                isCompanyAccountSwitcherComponent.disabled(companyIdData !== null);
                isCompanyAccountSwitcherComponent.value('0');
            }

            return this;
        },

        /**
         * Set the changed status of fieldset
         *
         * @param {Object} component
         * @param {Boolean} isClear
         * @private
         */
        _resetFieldset(component, isClear) {
            if (component.containers.length > 0) {
                component.containers.forEach(function (container) {
                    if (container.index === 'assign_to_company_account') {
                        container.changed(isClear);
                        this._resetFieldset(container, isClear);
                    }
                }.bind(this));
            }
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
