/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            // append extra data collected during checkout for server side processing
            // @see \Dhl\Shipping\Plugin\Checkout\ShippingInformationManagementPlugin
            shippingAddress['extension_attributes']['dhlshipping'] = {
                services: ['wunschtermin'],
                postalFacility: 'type, station id, postnumber'
            };

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});
