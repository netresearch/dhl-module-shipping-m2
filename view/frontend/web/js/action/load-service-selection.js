define([
    'underscore',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
], function (_, urlBuilder, customer, storage, quote) {
    'use strict';

    return function () {

        var url, urlParams, serviceUrl, payload;
        if (customer.isLoggedIn()) {
            url = '/carts/mine/dhl-services/load-services';
            urlParams = {};
        } else {
            url = '/guest-carts/:cartId/dhl-services/load-services';
            urlParams = {
                cartId: quote.getQuoteId()
            };
        }

        serviceUrl = urlBuilder.createUrl(url, urlParams);
        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        );
    };
});
