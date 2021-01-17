define([
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Bss_CustomerToSubUser/js/model/company-account'
], function (_, Select, CompanyAccount) {
    'use strict';

    return Select.extend({
        defaults: {
            elementTmpl: 'Bss_CustomerToSubUser/form/element/company-roles',
            wasAssigned: false,
            validation: {
                'required-entry': false
            }
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
            CompanyAccount.roleId.subscribe(this.whenRoleForceSelected, this);
            this.wasAssigned.subscribe(function (wasAssigned) {
                this.validation['required-entry'] = wasAssigned;
                this.additionalClasses._required(wasAssigned);

                this.error('');
            }, this);

            return this;
        },

        /**
         * Force selected a role by id
         *
         * @param {Number|null} roleId
         */
        whenRoleForceSelected: function (roleId) {
            console.log('subscriber: role updated');
            this._selectRole(roleId);
        },

        /**
         * Select role option
         *
         * @param {Number|undefined} roleId
         * @private
         */
        _selectRole: function (roleId) {
            var roleUser = CompanyAccount.roleUser(),
                companyAccount = CompanyAccount.data();

            try {
                if (companyAccount && roleUser &&
                    roleUser['entity_id'] == companyAccount['entity_id'] // eslint-disable-line eqeqeq
                ) {
                    roleId = roleUser['role_id'];
                }
            } catch (e) {
                console.error(e);
            }
            this.value(roleId);
        },

        /**
         * Filters 'initialOptions' property by 'field' and 'value' passed,
         * calls 'setOptions' passing the result to it
         *
         * @param {*} value
         * @param {String} field
         */
        filter: function (value, field) {
            var source = this.initialOptions,
                result;

            field = field || this.filterBy.field;

            result = _.filter(source, function (item) {
                // eslint-disable-next-line eqeqeq
                return item[field] == value || item.value === '' || item[field] === 'admin';
            });

            this.setOptions(result);
            this._selectRole();
            console.log('filter roles: value-' + value + '; ' + ', init value: ' + this.value());
        },

        /**
         * Update the visibility of component
         *
         * @param {Object|null} data
         */
        whenSelectCompanyAccount: function (data) {
            this.wasAssigned(Boolean(data && data.hasOwnProperty('entity_id')));
        }
    });
});
