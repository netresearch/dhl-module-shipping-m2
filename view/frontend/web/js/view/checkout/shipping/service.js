define([
    'underscore',
    'uiCollection',
    'uiLayout',
], function (_, Component, layout) {
    'use strict';

    return Component.extend({
        defaults: {
            service: {},
            validateWholeGroup: false
        },

        initialize: function () {
            this._super();
            var servicesLayout = _.map(this.service.inputs, function (serviceInput) {
                return this.getServiceFieldLayout(serviceInput);
            }, this);

            layout(servicesLayout);
        },

        getServiceFieldLayout: function (serviceInput) {
            return {
                parent: this.name,
                component: 'Dhl_Shipping/js/view/checkout/shipping/service-input',
                serviceInput: serviceInput,
                service: this.service,
            }
        }
    });
});
