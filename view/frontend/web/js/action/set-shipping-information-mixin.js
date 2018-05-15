define([
    'underscore',
    'mage/utils/wrapper',
    'Dhl_Shipping/js/model/services'
], function (wrapper, services) {
    'use strict';

    return function (setShippingInformationAction) {


        _.each(services, function(service) {
            console.log(service.value)
        });

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            return originalAction();
        });
    };
});
