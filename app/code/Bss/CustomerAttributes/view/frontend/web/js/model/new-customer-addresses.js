/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define([
    'underscore',
    'jquery',
    'mage/url',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function (_,$, urlManager, DefaultPostCodeResolver) {
    'use strict';

    /**
     * @param {Object} addressData
     * Returns new address object
     */
    return function (addressData) {
        var identifier = Date.now(),
            countryId = addressData['country_id'] || addressData.countryId || window.checkoutConfig.defaultCountryId,
            regionId;
        var custom = addressData['custom_attributes'];
        var bssCustomAttributes = [];
        function getValueOption(attribute) {
            return new Promise(function(resolve, reject) {
                $.ajax({
                    url: urlManager.build('customerattribute/index/getvalue'),
                    data: {'data': attribute},
                    dataType: 'json',
                    success: function(data) {
                        resolve(data) // Resolve promise and go to then()
                    },
                    error: function(err) {
                        reject(err) // Reject the promise and go to catch()
                    }
                });
            });
        }

        if (custom){
            Object.keys(custom).forEach(key => {
                if (custom[key]['value'] !== ''){
                    getValueOption(JSON.stringify(custom[key])).then(function(data) {
                            let result = JSON.parse(data['convertValue'])
                            if (result.label){
                                bssCustomAttributes.push(result);
                            }
                    }).catch(function(err) {
                        // Run this when promise was rejected via reject()
                        console.log(err)
                    })
                }
            });
        }

        if (addressData.region && addressData.region['region_id']) {
            regionId = addressData.region['region_id'];
        } else if (!addressData['region_id']) {
            regionId = undefined;
        } else if (
            /* eslint-disable */
            addressData['country_id'] && addressData['country_id'] == window.checkoutConfig.defaultCountryId ||
            !addressData['country_id'] && countryId == window.checkoutConfig.defaultCountryId
            /* eslint-enable */
        ) {
            regionId = window.checkoutConfig.defaultRegionId || undefined;
        }

        return {
            email: addressData.email,
            countryId: countryId,
            regionId: regionId || addressData.regionId,
            regionCode: addressData.region ? addressData.region['region_code'] : null,
            region: addressData.region ? addressData.region.region : null,
            customerId: addressData['customer_id'] || addressData.customerId,
            street: addressData.street ? _.compact(addressData.street) : addressData.street,
            company: addressData.company,
            telephone: addressData.telephone,
            fax: addressData.fax,
            postcode: addressData.postcode ? addressData.postcode : DefaultPostCodeResolver.resolve(),
            city: addressData.city,
            firstname: addressData.firstname,
            lastname: addressData.lastname,
            middlename: addressData.middlename,
            prefix: addressData.prefix,
            suffix: addressData.suffix,
            vatId: addressData['vat_id'],
            saveInAddressBook: addressData['save_in_address_book'],
            customAttributes: bssCustomAttributes,
            /**
             * @return {*}
             */
            isDefaultShipping: function () {
                return addressData['default_shipping'];
            },

            /**
             * @return {*}
             */
            isDefaultBilling: function () {
                return addressData['default_billing'];
            },

            /**
             * @return {String}
             */
            getType: function () {
                return 'new-customer-address';
            },

            /**
             * @return {String}
             */
            getKey: function () {
                return this.getType();
            },

            /**
             * @return {String}
             */
            getCacheKey: function () {
                return this.getType() + identifier;
            },

            /**
             * @return {Boolean}
             */
            isEditable: function () {
                return true;
            },

            /**
             * @return {Boolean}
             */
            canUseForBilling: function () {
                return true;
            }
        };
    };
});
