define([
    'Magento_Ui/js/grid/listing'
], function (Listing) {
    'use strict';

    return Listing.extend({
        defaults: {
            editorConfig: {
                component: "Bss_CustomizeCompanyAccount/js/grid/editing/editor"
            }
        }
    });
});
