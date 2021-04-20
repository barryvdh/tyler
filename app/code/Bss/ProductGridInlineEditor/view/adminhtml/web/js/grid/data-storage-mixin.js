/**
* BSS Commerce Co.
*
* NOTICE OF LICENSE
*
* This source file is subject to the EULA
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://bsscommerce.com/Bss-Commerce-License.txt
*
* @category   BSS
* @package    Bss_ProductGridInlineEditor
* @author     Extension Team
* @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
* @license    http://bsscommerce.com/Bss-Commerce-License.txt
*/
define(function () {
    'use strict';

    return function (dataStorage) {
        return dataStorage.extend({
            /**
             * Extracts data which matches specified parameters.
             *
             * @param {Object} params - Request parameters.
             * @param {Object} [options={}]
             * @returns {jQueryPromise}
             */
            getData: function (params, options) {
                var cachedRequest;

                if (this.hasScopeChanged(params)) {
                    this.clearRequests();
                } else {
                    cachedRequest = this.getRequest(params);
                }

                options = options || {};

                if (params.hasOwnProperty('namespace') && params.namespace == "product_listing" 
                    && (typeof window.hasRefresh != 'undefined')) {
                    options.refresh = true;
                }

                return !options.refresh && cachedRequest ?
                    this.getRequestData(cachedRequest) :
                    this.requestData(params);
            },
        });
    };
});