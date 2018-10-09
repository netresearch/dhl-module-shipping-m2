define([
    'mage/utils/wrapper',
    'Dhl_Shipping/js/action/validate-service-selection',
    'Dhl_Shipping/js/action/save-service-selection'
], function (wrapper, validateServices, saveServices) {
    'use strict';

    /**
     * Intercept click on "Next" button in checkout
     * to validate and save service input values.
     */
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if (validateServices()) {
                return saveServices().done(originalAction);
            } else {
                // do nothing
                return {
                    'done': function () {
                    }
                }
            }
        });
    }
});
