define([
    'Magento_Downloadable/js/components/is-downloadable-handler'
], function (IsDownloadableHandler) {
    'use strict';

    return IsDownloadableHandler.extend({
        /**
         * Always check the component n  hide it
         *
         * @returns {IsDownloadableHandler}
         */
        initialize: function () {
            this._super();

            this.checked(true);
            this.visible(false);

            return this;
        }
    });
});
