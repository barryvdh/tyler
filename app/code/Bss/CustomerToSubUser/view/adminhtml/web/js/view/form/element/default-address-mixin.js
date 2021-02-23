define([], function () {
    'use strict';

    var disabledCustomAttributeMixin = {
        defaults: {
            imports: {
                isSubUser: '${ $.provider }:data.assign_to_company_account.is_sub_user'
            }
        },

        /**
         * Hide the edit and add address button if current customer is sub-user
         *
         * @returns {disabledCustomAttributeMixin}
         */
        initialize: function () {
            this._super();

            if (this.isSubUser) {
                if (typeof this.visible === 'function') {
                    this.visible(false);
                } else {
                    this.visible = false;
                }
            }

            return this;
        }
    };

    return function (target) {
        return target.extend(disabledCustomAttributeMixin);
    };
});
