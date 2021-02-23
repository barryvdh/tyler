define([
    'Magento_Customer/js/grid/columns/actions'
], function (Action) {
    'use strict';

    return Action.extend({
        /**
         * Remove address action if current customer is sub-user
         *
         * @param {Number} rowIdx
         * @returns {Array}
         */
        getVisibleActions: function (rowIdx) {
            if (this.companyAccountId) {
                return [];
            }

            return this._super(rowIdx);
        }
    });
});
