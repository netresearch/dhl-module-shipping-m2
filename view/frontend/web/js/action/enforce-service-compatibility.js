define([
    'underscore',
    'uiRegistry',
    'Dhl_Shipping/js/model/services',
    'Dhl_Shipping/js/model/service-compatibility',
], function (_, registry, serviceSelection, serviceCompatibility) {
    'use strict';

    /**
     * Check for unavailable service combinations and trigger field disable events for Service Block.
     *
     * @return {boolean}
     */
    return function () {
        var compatibilityInfo = serviceCompatibility.getData(),
            selectedServiceCodes = [],
            serviceBlock = registry.get({component: 'Dhl_Shipping/js/view/checkout/shipping/service-block'});

        for (var service in serviceSelection.get()()) {
            selectedServiceCodes.push(service)
        }

        _.each(compatibilityInfo, function (compatibility) {
            if (compatibility.type === 'exclusive') {
                var servicesWithCompatibilityData = _.intersection(compatibility.subject, selectedServiceCodes);
                if (servicesWithCompatibilityData.length > 0) {
                    var servicesToBeDisabled = _.difference(compatibility.subject, servicesWithCompatibilityData);
                    servicesToBeDisabled.map(function (serviceCode) {
                        serviceBlock.triggerDisableInput(serviceCode);
                    });
                } else {
                    compatibility.subject.map(function (serviceCode) {
                        serviceBlock.triggerEnableInput(serviceCode);
                    });
                }
            }
        }.bind(selectedServiceCodes));
    };
});
