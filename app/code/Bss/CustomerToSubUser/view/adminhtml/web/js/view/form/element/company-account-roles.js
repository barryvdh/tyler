define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Bss_CustomerToSubUser/js/model/company-account',
    'Bss_CustomerToSubUser/js/service/RESTfulService'
], function ($, ko, Select, CompanyAccount, service) {
    'use strict';

    return Select.extend({
        defaults: {
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-roles',
            wasAssigned: false,
            listens: {
                'params.selectedRole': 'whenRoleUpdate'
            }
        },

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

            this.observe('wasAssigned');

            CompanyAccount.data.subscribe(this.whenSelectCompanyAccount, this);

            return this;
        },

        /**
         * Update the visibility of component
         *
         * @param {Object|null} data
         */
        whenSelectCompanyAccount: function (data) {
            var visible = false,
                request,
                options = [];

            if (data && data.hasOwnProperty('entity_id')) {
                try {
                    request = service.getRolesByCompanyAccountId(data['entity_id'], data['website_id']);
                    request.done(function (res) {
                        options = res.map(function (item) {
                            return {
                                label: item['role_name'],
                                value: item['role_id']
                            };
                        });

                        this.options(options);
                        this.value(
                            CompanyAccount.roleUser() ?
                                CompanyAccount.roleUser()['entity_id'] === CompanyAccount.data()['entity_id'] ?
                                    CompanyAccount.roleUser()['role_id'] : null : null
                        );
                    }.bind(this));
                } catch (e) {
                    console.error(e);
                }

                visible = true;
            }

            this.wasAssigned(visible);
        }
    });
});
