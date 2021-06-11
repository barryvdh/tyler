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

            return this;
        },

        /**
         * Change visibility for samplesFieldset & linksFieldset based on current statuses of checkbox.
         */
        changeVisibility: function () {
            if (this.linksFieldset()) {
                if (this.checked() && !this.disabled()) {
                    this.linksFieldset().visible(true);
                } else {
                    this.linksFieldset().visible(false);
                }
            }
        },
    });
});
