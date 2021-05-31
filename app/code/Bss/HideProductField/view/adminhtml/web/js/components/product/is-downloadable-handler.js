define([
    'Magento_Downloadable/js/components/is-downloadable-handler'
], function (IsDownloadableHandler) {
    'use strict';

    return IsDownloadableHandler.extend({
        defaults: {
            titleField: 'ns = ${ $.ns }, index=links_title',
            modules: {
                titleField: '${ $.titleField }'
            }
        },
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

            if (this.titleField()) {
                this.titleField().visible(false);
            }

            return this;
        }
    });
});
