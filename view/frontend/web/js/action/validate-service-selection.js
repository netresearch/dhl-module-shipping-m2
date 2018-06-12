define([
    'underscore',
    'uiRegistry',
    'Dhl_Shipping/js/model/services',
    'Dhl_Shipping/js/model/service-compatibility',
], function (_, registy, serviceSelection, serviceCompatibility) {
    'use strict';

    /**
     * Check for unavailable service combinations and trigger validation events for Service Block.
     *
     * @return {boolean}
     */
    var validateCompatibility = function () {
        var compatibilityInfo = serviceCompatibility.getData(),
            result = true,
            selectedServiceCodes = [],
            serviceBlock = registy.get({component: 'Dhl_Shipping/js/view/checkout/shipping/service-block'});

        for (var service in serviceSelection.get()()) {
            selectedServiceCodes.push(service)
        }

        _.each(compatibilityInfo, function (compatibility) {
            if (compatibility.type === 'exclusive') {
                var exclusivityViolated = _.difference(compatibility.subject, selectedServiceCodes).length === 0;
                if (exclusivityViolated) {
                    serviceBlock.triggerValidationFail(compatibility);
                    result = false;
                }
            } else if (compatibility.type === 'inclusive') {
                var inclusivityViolated = _.difference(compatibility.subject, selectedServiceCodes).length !== 0;
                if (inclusivityViolated) {
                    serviceBlock.triggerValidationFail(compatibility);
                    result = false;
                }
            }
        }.bind(selectedServiceCodes));

        if (result) {
            serviceBlock.triggerValidationSuccess();
        }
        return result;
    };


    /**
     * Trigger built in form input component validation of service inputs.
     *
     * @return {boolean}
     */
    var validateValues = function () {
        var inputs = registy.filter({component: 'Dhl_Shipping/js/view/checkout/shipping/service-input'}),
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

        var compatibilityValid = validateCompatibility();
        var valuesValid = validateValues();
        return compatibilityValid && valuesValid;
    };
});
