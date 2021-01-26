define([
    'Magento_Customer/js/grid/massactions'
], function (MassActions) {
    'use strict';

    return MassActions.extend({
        /**
         * If the customer is sub-user then load custom template
         *
         * @returns {String}
         */
        getTemplate: function () {
            if (this.companyAccountId) {
                return 'Bss_CustomerToSubUser/grid/massactions';
            }

            return this._super();
        }
    });
});
