define([
    'Magento_Ui/js/form/element/image-uploader'
], function (Field) {
    'use strict';

    return Field.extend({

        /**
         * Visible on brand category
         */
        initialize: function () {
            this._super();
            this.visible(
                this.source.data.level === '3'
            );

            return this;
        }
    });
});
