define([
    'jquery',
    'mage/url',
    'mage/storage'
], function ($, urlBuilder, storage) {
    'use strict';

    var getRolesByCompanyAccountIdApiPath = 'company-account/roles/company/:emailOrId/website/:websiteId';

    return {
        method: 'rest',
        version: 'V1',
        serviceUrl: '/:method/:version/',

        /**
         * Get list roles by company account id
         *
         * @param {Number} companyAccountId
         * @param {Number} websiteId
         * @returns {*}
         */
        getRolesByCompanyAccountId: function (companyAccountId, websiteId) {
            var serviceUrl = this.getUrl(
                getRolesByCompanyAccountIdApiPath,
                {
                    emailOrId: companyAccountId,
                    websiteId: websiteId
                }
            );

            return storage.get(serviceUrl);
        },

        /**
         * Get url
         *
         * @param {String} url
         * @param {Object} params
         */
        getUrl: function (url, params) {
            url = this.bindParams(url, params);

            return urlBuilder.build(url);
        },

        /**
         * Bind request params
         *
         * @param {String} url
         * @param {Object} params
         * @return {*}
         */
        bindParams: function (url, params) {
            var urlParts;

            url = this.serviceUrl + url;

            params.method = this.method;
            params.version = this.version;

            urlParts = url.split('/');
            urlParts = urlParts.filter(Boolean);

            $.each(urlParts, function (key, part) {
                part = part.replace(':', '');

                if (params[part] != undefined) { //eslint-disable-line eqeqeq
                    urlParts[key] = params[part];
                }
            });

            return '/' + urlParts.join('/');
        }
    };
});
