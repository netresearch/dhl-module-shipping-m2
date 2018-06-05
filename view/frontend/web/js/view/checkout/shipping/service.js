define([
    'underscore',
    'uiCollection',
    'uiLayout',
    'Dhl_Shipping/js/model/service-validation-map'
], function (_, Component, layout, serviceValidationMap) {
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
            var validation = {};
            _.each(serviceInput.validationRules, function (value, rule) {
                var validatorName = serviceValidationMap.getValidatorName(rule);
                if (validatorName) {
                    validation[validatorName] = value;
                } else {
                    console.warn('DHL service validation rule ' + validation + ' is not defined.');
                }
            });

            return {
                parent: this.name,
                component: 'Dhl_Shipping/js/view/checkout/shipping/service-input',
                serviceInput: serviceInput,
                service: this.service,
                validation: validation
            }
        }
    });
});
