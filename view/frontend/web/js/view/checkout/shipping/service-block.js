define([
    'uiComponent',
    'ko',
    'Dhl_Shipping/js/model/services-data',
    'Magento_Checkout/js/model/quote'
], function (Component, ko, serviceData, quote) {

    'use strict';

    return Component.extend({
        defaults: {
            template: 'Dhl_Shipping/checkout/shipping/service-block'
        },

        isDhlMethod: function () {
            var dhlMethods = serviceData.getDhlMethods();
            var selectedShippingMethod  = quote.shippingMethod();
            var carrierCode = selectedShippingMethod.carrier_code + '_' + selectedShippingMethod.method_code;

            if (dhlMethods.indexOf(carrierCode) != -1) {
                return true;
            }
            return false;
        }
    });
});
