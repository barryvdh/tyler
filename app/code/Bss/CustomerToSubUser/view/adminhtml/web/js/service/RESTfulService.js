define([
    'jquery',
    'mage/url',
    'mage/storage'
], function ($, urlBuilder, storage) {
    'use strict';

    var getRolesByCompanyAccountIdApiPath = 'company-account/roles/company/:emailOrId/website/:websiteId',
        getCompanyAccountBySubEmail = 'company-account/company-account/:email/:websiteId',
        getCompanyAccountCustomAttributes = 'company-account/custom-attributes/:customerId';

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
         * Get company account data
         *
         * @param {String} email
         * @param {Number} websiteId
         * @returns {*}
         */
        getAssignedCompanyAccount: function (email, websiteId) {
            var serviceUrl = this.getUrl(
                getCompanyAccountBySubEmail,
                {
                    email: email,
                    websiteId: websiteId
                }
            );

            return storage.get(serviceUrl);
        },

        /**
         * Get company account custom attributes
         *
         * @param {Number} id
         * @returns {Array|Boolean}
         */
        getCompanyAccountAttributes: function (id) {
            var serviceUrl = this.getUrl(
                getCompanyAccountCustomAttributes,
                {
                    customerId: id
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
