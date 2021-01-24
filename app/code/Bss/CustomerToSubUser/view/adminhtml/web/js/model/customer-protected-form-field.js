define(function () {
    'use strict';

    return {
        protectedFields: [
            'website_id',
            'group_id',
            'disable_auto_group_change',
            'container_group',
            'prefix',
            'firstname',
            'middlename',
            'lastname',
            'suffix',
            'email',
            'dob',
            'taxvat',
            'gender',
            'sendemail_store_id',
            'vertex_customer_code',
            'b2b_activasion_status',
            'company_account_id',
            'role_id',
            'sub_id'
        ],
        noneCopyFields: [
            'bss_is_company_account',
            'b2b_activasion_status'
        ],

        /**
         * Get None copy fields
         *
         * @returns {Array}
         */
        getNoneCopyFields: function () {
            return this.noneCopyFields;
        },

        /**
         * Add none copy field
         *
         * @param {String} field
         */
        addNoneCopyField: function (field) {
            this.noneCopyFields.push(field);
        },

        /**
         * Get customer information protected fields
         *
         * @returns {String[]}
         */
        getProtectedFields: function () {
            return this.protectedFields;
        },

        /**
         * For custom field should not be disable
         *
         * @param {String} field
         */
        addProtectedField: function (field) {
            this.protectedFields.push(field);
        }
    };
});
