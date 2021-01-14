define([
    'ko',
    'Magento_Ui/js/grid/columns/multiselect',
    'Bss_CustomerToSubUser/js/model/company-account'
], function (ko, Multiselect, CompanyAccount) {
    'use strict';

    return Multiselect.extend({
        defaults: {
            headerTmpl: 'Bss_CustomerToSubUser/grid/columns/multiselect',
            bodyTmpl: 'Bss_CustomerToSubUser/grid/cells/multiselect',
            hasSelected: ko.observable(false),
            listens: {
                params: 'whenParamsWereUpdate'
            }
        },

        whenParamsWereUpdate: function (value) {
            console.log(value);
        },

        /**
         * Initializes column component.
         * Remove all the select actions
         *
         * @returns {Column} Chainable.
         */
        initialize: function () {
            var parentObj = this._super();

            this.actions.slice(0, 3);

            return parentObj;
        },

        /**
         * Remove the "Select All On This Page" functionality
         */
        selectPage: function () {
            return this;
        },

        /**
         * Initializes observable properties.
         *
         * @returns {Multiselect} Chainable.
         */
        initObservable: function () {
            this._super();

            this.selected.subscribe(this.whenSelectedChange, this);
            CompanyAccount.data.subscribe(this.whenCompanyAccountUpdate, this);

            return this;
        },

        /**
         * If company account data was removed, clear the select in grid if exists
         *
         * @param {Object} companyAccData
         */
        whenCompanyAccountUpdate: function (companyAccData) {
            if (companyAccData === null && this.selected().length > 0) {
                this.selected([]);
            }
        },

        /**
         * When row is selected
         *
         * @param {Array} ids
         * @returns {*}
         */
        whenSelectedChange: function (ids) {
            var companyAccount, selectedId, checkboxVisibility = false;

            if (ids.length === 1 && CompanyAccount.data() !== null) {
                return;
            }

            if (ids.length > 0 && CompanyAccount.data() == null) {
                selectedId = ids.shift();

                //jscs:disable jsDoc
                companyAccount = this.rows().find(function (customer) {
                    return customer[this.indexField] === selectedId;
                }.bind(this));

                if (!companyAccount) {
                    this.selected([]);

                    return this;
                }

                if (companyAccount) {
                    CompanyAccount.data(companyAccount);
                    this.selected([selectedId]);
                    checkboxVisibility = selectedId;
                }
            } else {
                CompanyAccount.data(null);
            }

            this.hasSelected(checkboxVisibility);
        },

        /**
         * Prevent select all function
         *
         * @returns {Multiselect} Chainable.
         */
        selectAll: function () {
            return this;
        },

        /**
         * Prevent deselect all function
         *
         * @returns {Multiselect} Chainable
         */
        deselectAll: function () {
            return this;
        }
    });
});
