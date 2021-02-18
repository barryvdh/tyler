define([
    'Magento_Ui/js/form/element/textarea'
], function (Component) {
    'use strict';

    return Component.extend({
        /**
         * Visible on brand category
         */
        initialize: function () {
            this._super();
            this.visible(this.source.data.level === '3');

            return this;
        }
    });
});
