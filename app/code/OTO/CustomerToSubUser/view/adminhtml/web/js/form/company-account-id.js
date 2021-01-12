define([
    'Magento_Ui/js/form/element/abstract'
], function (Field) {
    'use strict';

    return Field.extend({
        initialize: function () {
            this._super();

            console.log(this.elementTmpl);
            return this;
        }
    });
});
