define([
    'jquery'
], function ($) {
    'use strict';

    var roleTypeMixin = {
        setBase: function (imageData) {
            if (this.options.types.hasOwnProperty('image')) {
                return this._super(imageData);
            }
        }
    };

    return function (targetWidget) {
        $.widget('mage.productGallery', targetWidget, roleTypeMixin);

        return $.mage.productGallery;
    }
});
