define([
    'ko',
    'Magento_Checkout/js/model/quote',
], function (ko, quote) {
    'use strict';

    /**
     * Holds list of definitions for checkout services.
     * Items adhere to Dhl\Shipping\Api\Data\ServiceInterface;
     *
     * @type {Object}
     */
    debugger;

    return {
        get: function () {
            if (!services) {
                var countryId = quote.shippingAddress().countryId,
                    carrierCode = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code,
                    services = storage.get(countryId + carrierCode) ? ko.observable(storage.get(countryId + carrierCode)) : ko.observable({});
                console.log(services());
            }
            return services;
        },

        set: function (data) {
            services(data)
        },
    };
});
