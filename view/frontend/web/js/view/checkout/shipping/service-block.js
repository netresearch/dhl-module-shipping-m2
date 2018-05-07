define([
    'underscore',
    'uiComponent',
    'ko',
    'Dhl_Shipping/js/action/get-services',
    'Magento_Checkout/js/model/quote'
], function (_, Component, ko, serviceAction, quote) {

    'use strict';

    var services = ko.observableArray([]);

    quote.shippingMethod.subscribe(function () {
        var countryId = quote.shippingAddress().countryId;
        var carrierCode = quote.shippingMethod().carrier_code;
        if (countryId && carrierCode) {
            var callback = function (result) {
                services(result);
            };

            serviceAction(countryId, carrierCode, callback);
        }
    });

    return Component.extend({
        defaults: {
            template: 'Dhl_Shipping/checkout/shipping/service-block'
        },

        getServices: function () {
            return services();
        }
    });
});
