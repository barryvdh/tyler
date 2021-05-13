define([
    'Magento_Ui/js/grid/editing/editor'
], function (Editor) {
    'use strict';

    return Editor.extend({
        /**
         * Disable inline edit for sub-user
         *
         * @param {Number|string} id
         * @param {Boolean} isIndex
         * @returns {*}
         */
        edit: function (id, isIndex) {
            if (this.isSubId(id)) {
                return this;
            }

            return this._super(id, isIndex);
        },

        /**
         * Disable inline edit for sub-user
         *
         * @param {Number|string} id
         * @param {Boolean} isIndex
         * @returns {*}
         */
        startEdit: function (id, isIndex) {
            var recordId = this.getId(id, isIndex), checkId = id;

            if (isIndex) {
                checkId = recordId;
            }

            if (this.isSubId(checkId)) {
                return this;
            }

            return this._super(id, isIndex);
        },

        /**
         * Check is sub-id in customer grid
         *
         * @param {String|Number} id
         */
        isSubId: function (id) {
            var found;

            if (Number.isInteger(id)) {
                return false;
            }

            const pattern = /^sub-([0-9]+)/g;
            found = id.match(pattern);

            return found && found.length > 0;
        }
    });
});
