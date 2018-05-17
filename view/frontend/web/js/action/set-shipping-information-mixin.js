define([
    'mage/utils/wrapper',
    'Dhl_Shipping/js/action/save-service-selection'
], function (wrapper, saveServices) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            saveServices();
            return originalAction();
        });
    }
});
