define([
    'underscore',
    'uiRegistry',
    'Dhl_Shipping/js/model/services',
    'Dhl_Shipping/js/model/service-compatibility',
], function (_, registry, serviceSelection, serviceCompatibility) {
    'use strict';

    /**
     * Check for unavailable service combinations and trigger validation events for Service Block.
     *
     * @return {boolean}
     */
    return function () {
        var compatibilityInfo = serviceCompatibility.getData(),
            result = true,
            selectedServiceCodes = [],
            serviceBlock = registry.get({component: 'Dhl_Shipping/js/view/checkout/shipping/service-block'});

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

});
