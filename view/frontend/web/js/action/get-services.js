define([
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-service',
    'Dhl_Shipping/js/model/service-compatibility',
    'Dhl_Shipping/js/model/service-definitions',
    'Dhl_Shipping/js/model/dhl-methods',
    'Dhl_Shipping/js/model/storage',
], function (
    urlBuilder,
    customer,
    request,
    quote,
    shippingService,
    serviceCompatibility,
    serviceDefinitions,
    dhlMethods,
    storage
) {
    'use strict';

    /**
     * @return {string}
     */
    var buildRequestUrl = function () {
        var url, urlParams;
        if (customer.isLoggedIn()) {
            url = '/carts/mine/dhl-services/get-services';
            urlParams = {};
        } else {
            url = '/guest-carts/:cartId/dhl-services/get-services';
            urlParams = {
                cartId: quote.getQuoteId()
            };
        }

        return urlBuilder.createUrl(url, urlParams);
    };

    /**
     * Automatically update service definition and compatibility models with new data.
     *
     * @param {Object} data
     */
    var updateModels = function (data) {
        serviceCompatibility.set(data.compatibility);
        serviceDefinitions.set(data.services);
        dhlMethods.set(data.methods);
    };

    /**
     * Load service Definitions from server. Uses local storage to cache responses.
     *
     * @param {string} countryId
     * @param {string} shippingMethod
     */
    return function (countryId, shippingMethod, postalCode) {
        var fromCache = storage.get(countryId + shippingMethod + postalCode);
        if (fromCache) {
            updateModels(fromCache);
            return;
        }

        var serviceUrl = buildRequestUrl(),
            payload = {countryId: countryId, shippingMethod: shippingMethod, postalCode:postalCode};

        shippingService.isLoading(true);
        request.post(
            serviceUrl,
            JSON.stringify(payload)
        ).success(
            function (response) {
                storage.set(countryId + shippingMethod + postalCode, response);
                updateModels(response);
            }
        ).always(
            function () {
               shippingService.isLoading(false);
            }
        );
    };
});
