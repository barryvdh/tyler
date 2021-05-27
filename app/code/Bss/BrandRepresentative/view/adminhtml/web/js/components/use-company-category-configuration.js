define([
    'ko',
    'Magento_Ui/js/form/element/select'
], function (ko, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            disabledBrandRepresentativeEmail: true
        },

        /**
         * Calls 'initObservable' of parent, initializes 'isUsedCompanyConfig' properties
         */
        initObservable: function () {
            this._super();
            this.observe('disabledBrandRepresentativeEmail');

            return this;
        },

        /**
         * Calls 'onUpdate' of parent,
         * set the disabled Brand Representative email field value
         */
        onUpdate: function () {
            this._super();

            this.disabledBrandRepresentativeEmail(Boolean(Number(this.value())));
        },

        /**
         * Sets initial value of the element and subscribes to it's changes.
         * Set init disabled brand representative email field
         *
         * @returns {*}
         */
        setInitialValue: function () {
            this._super();

            this.disabledBrandRepresentativeEmail(Boolean(Number(this.initialValue)));

            return this;
        }
    });
});
