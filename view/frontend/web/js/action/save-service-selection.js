define([
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Dhl_Shipping/js/model/services'
], function (urlBuilder, customer, storage, quote, services) {
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

        payload = {serviceSelection: services.getServices()};
        serviceUrl = urlBuilder.createUrl(url, urlParams);

        return storage.post(
            serviceUrl,
            JSON.stringify(payload)
        );
    };
});
