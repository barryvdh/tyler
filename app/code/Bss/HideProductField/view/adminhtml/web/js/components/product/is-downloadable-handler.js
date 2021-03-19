define([
    'Magento_Downloadable/js/components/is-downloadable-handler'
], function (IsDownloadableHandler) {
    'use strict';

    return IsDownloadableHandler.extend({
        /**
         * Always check the component n  hide it
         * Also hide the  samples fieldset
         *
         * @returns {IsDownloadableHandler}
         */
        initialize: function () {
            this._super();

            this.checked(true);
            this.visible(false);
            if (this.samplesFieldset()) {
                this.samplesFieldset().visible(false);
            }

            return this;
        }
    });
});
