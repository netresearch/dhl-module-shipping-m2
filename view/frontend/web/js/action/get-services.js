/**
 * Refer to LICENSE.txt distributed with the Temando Shipping module for notice of license
 */

define([
    'underscore',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service'
], function (_, urlBuilder, customer, storage, quote, shippingService) {
    'use strict';

    return function (countryId, shippingMethod, successCallback) {

        shippingService.isLoading(true);
        var url, urlParams, serviceUrl, payload;
        if (customer.isLoggedIn()) {
            url = '/carts/mine/dhl-services';
            urlParams = {};
        } else {
            url = '/guest-carts/:cartId/dhl-services';
            urlParams = {
                cartId: quote.getQuoteId()
            };
        }


        payload = {countryId: countryId, shippingMethod: shippingMethod};
        serviceUrl = urlBuilder.createUrl(url, urlParams);

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        ).success(
            function (response) {
                successCallback(response);
            }
        ).always(
            function () {
                shippingService.isLoading(false);
            }
        );
    };
});
