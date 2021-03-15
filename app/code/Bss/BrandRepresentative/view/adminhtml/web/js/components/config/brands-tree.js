define([
    'Magento_Ui/js/form/element/ui-select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            template: "ui/form/field",
            elementTmpl: "Bss_BrandRepresentative/brands",
            labelVisible: false,
            filterOptions: true,
            chipsEnabled: true,
            multiple: true,
            showCheckbox: true,
            levelsVisibility: 2,
            validationLoading: true
        },

        /**
         * Initializes UISelect component.
         *
         * @returns {Select} Chainable.
         */
        initialize: function () {
            this._super();

            if (this.selectedBrands) {
                this.value(this.selectedBrands);
            }

            return this;
        }
    });
});
