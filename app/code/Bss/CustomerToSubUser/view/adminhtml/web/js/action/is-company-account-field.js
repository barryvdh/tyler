define([
    'uiRegistry'
], function (uiRegistry) {
    'use strict';

    var isCompanyAccountAttributeSelector = 'index = bss_is_company_account';

    return {
        /**
         * Force ensure that the is company account attribute is '0'
         * if the current customer was assigned as company account
         *
         * @param {Boolean} disable
         */
        toggle: function (disable) {
            var isCompanyAccountSwitcherComponent = uiRegistry.get(isCompanyAccountAttributeSelector);

            if (isCompanyAccountSwitcherComponent) {
                console.log('change state of component: ' + disable);
                isCompanyAccountSwitcherComponent.disabled(disable);
                isCompanyAccountSwitcherComponent.value('0');
            }
        }
    };
});
