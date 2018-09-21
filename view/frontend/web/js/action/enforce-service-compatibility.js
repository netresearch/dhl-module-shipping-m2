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
    return function () {
        var compatibilityInfo = serviceCompatibility.getData(),
            selectedServiceCodes = [],
            serviceBlock = registy.get({component: 'Dhl_Shipping/js/view/checkout/shipping/service-block'});

        for (var service in serviceSelection.get()()) {
            selectedServiceCodes.push(service)
        }

        _.each(compatibilityInfo, function (compatibility) {
            if (compatibility.type === 'exclusive') {
                var serv = _.intersection(compatibility.subject, selectedServiceCodes);
                if (serv.length > 0) {
                    var serviceToBeDisabled = _.difference(compatibility.subject, serv);
                    serviceToBeDisabled.map(function (serviceCode) {
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
