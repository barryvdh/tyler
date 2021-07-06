define([
    'jquery'
], function ($) {
    'use strict';

    var roleTypeMixin = {
        setBase: function (imageData) {
            if (this.options.types.hasOwnProperty('image')) {
                return this._super(imageData);
            }
        },

        /**
         * Set image
         *
         * @param {jQuery.Event} event
         * @private
         */
        _notifyType: function (event) {
            if ($(event.currentTarget).data('set-all')) {
                var $checkbox = $(event.currentTarget),
                    $imageContainer = $checkbox.closest('[data-role=dialog]').data('imageContainer');
                Object.entries(this.options.types).forEach(function ([key, type]) {
                    this.element.trigger('setImageType', {
                        type: key,
                        imageData: $checkbox.is(':checked') ? $imageContainer.data('imageData') : null
                    });
                }, this);
            } else {
                this._super(event);
            }
        },

        /**
         * Handles dialog open event.
         *
         * Custom checkbox apply all image role to image
         *
         * @param {EventObject} event
         */
        onDialogOpen: function (event) {
            var imageData = this.$dialog.data('imageData'),
                imageSizeKb = imageData.sizeLabel,
                image = document.createElement('img'),
                sizeSpan = this.$dialog.find(this.options.imageSizeLabel)
                    .find('[data-message]'),
                resolutionSpan = this.$dialog.find(this.options.imageResolutionLabel)
                    .find('[data-message]'),
                sizeText = sizeSpan.attr('data-message').replace('{size}', imageSizeKb),
                resolutionText;

            image.src = imageData.url;

            resolutionText = resolutionSpan
                .attr('data-message')
                .replace('{width}^{height}', image.width + 'x' + image.height);

            sizeSpan.text(sizeText);
            resolutionSpan.text(resolutionText);

            // Custom: use one checkbox instead multiple select
            $(event.target)
                .find('[data-role=type-selector]')
                .each($.proxy(function (index, checkbox) {
                    var $checkbox = $(checkbox),
                        isChecked = true,
                        parent = $checkbox.closest('.item'),
                        selectedClass = 'selected';

                    if ($checkbox.data('set-all')) {
                        Object.entries(this.options.types).forEach(function ([key, type]) {
                            //eslint-disable-next-line
                            if (type.value != imageData.file) {
                                isChecked = false;
                            }
                        });

                        $checkbox.prop('checked', isChecked);
                        return false;
                    }

                    isChecked = this.options.types[$checkbox.val()].value == imageData.file; //eslint-disable-line
                    $checkbox.prop(
                        'checked',
                        isChecked
                    );
                    parent.toggleClass(selectedClass, isChecked);
                }, this));
        }
    };

    return function (targetWidget) {
        $.widget('mage.productGallery', targetWidget, roleTypeMixin);

        return $.mage.productGallery;
    }
});
