define([
    'Magento_Ui/js/form/element/abstract'
], function (Textbox) {
    'use strict';

    return Textbox.extend({
        /**
         * If the sale qty per month
         * @returns {Textbox}
         */
        initialize: function () {
            var stockData;
            this._super();

            if (this.source && this.source.data && this.source.data.product) {
                stockData = this.source.data.product['stock_data'] || null;

                if (stockData['use_config_sale_qty_per_month'] === "0") {
                    if (typeof this.value === 'function') {
                        this.value(null);
                    } else {
                        this.value = null;
                    }
                }
            }

            return this;
        }
    });
});
