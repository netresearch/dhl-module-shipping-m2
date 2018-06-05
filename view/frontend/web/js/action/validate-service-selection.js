define([
    'underscore',
    'uiRegistry'
], function (_, registy) {
    'use strict';

    /**
     * Run uiComponent validators for DHL Service components and
     * return the composite validation result.
     *
     * @return {bool}
     */
    return function () {
        var result = true;
        var inputs = registy.filter({component: 'Dhl_Shipping/js/view/checkout/shipping/service-input'});
        _.each(inputs, function (input) {
            var validationResult = input.validate();
            if (!validationResult.valid) {
                result = false;
            }
        });

        return result;
    };
});
