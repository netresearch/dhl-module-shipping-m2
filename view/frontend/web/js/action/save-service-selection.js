define([
    'underscore',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Dhl_Shipping/js/model/services'
], function (_, urlBuilder, customer, storage, quote, serviceSelection) {
    'use strict';

    return function () {

        var url, urlParams, serviceUrl, payload;
        if (customer.isLoggedIn()) {
            url = '/carts/mine/dhl-services/save-services';
            urlParams = {};
        } else {
            url = '/guest-carts/:cartId/dhl-services/save-services';
            urlParams = {
                cartId: quote.getQuoteId()
            };
        }
        payload = {
            serviceSelection: [],
            shippingMethod: quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code,
        };
        _.each(serviceSelection.get()(), function (value, key) {
            payload.serviceSelection.push(
                {
                    attribute_code: key,
                    value: value
                }
            );
        });

        serviceUrl = urlBuilder.createUrl(url, urlParams);
        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        );
    };
});
