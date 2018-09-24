define([
    'underscore',
    'uiRegistry',
    'Dhl_Shipping/js/action/validate-service-compatibility',
], function (_, registry, validateServiceCompatibility) {
    'use strict';



    /**
     * Trigger built in form input component validation of service inputs.
     *
     * @return {boolean}
     */
    var validateValues = function () {
        var inputs = registry.filter({component: 'Dhl_Shipping/js/view/checkout/shipping/service-input'}),
            result = true;

        _.each(inputs, function (input) {
            var validationResult = input.validate();
            if (!validationResult.valid) {
                result = false;
            }
        });

        return result;
    };

    /**
     * Run uiComponent validators for DHL Service components and
     * return the composite validation result.
     *
     * @return {bool}
     */
    return function () {
        var compatibilityValid = validateServiceCompatibility();
        var valuesValid = validateValues();

        return compatibilityValid && valuesValid;
    };
});
