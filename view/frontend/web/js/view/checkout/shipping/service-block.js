define([
    'underscore',
    'uiCollection',
    'uiLayout',
    'ko',
    'Dhl_Shipping/js/action/get-services',
    'Magento_Checkout/js/model/quote',
    'Dhl_Shipping/js/model/services',
    'Dhl_Shipping/js/model/service-validation-map'
], function (_, UiCollection, layout, ko, serviceAction, quote, serviceModel, serviceValidationMap) {

    'use strict';

    var services = ko.observableArray([]);

    quote.shippingMethod.subscribe(function () {
        var countryId = quote.shippingAddress().countryId;
        var carrierCode = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
        if (countryId && carrierCode) {
            var callback = function (result) {
                services(result);
            };

            serviceAction(countryId, carrierCode, callback);
        }
    });

    return UiCollection.extend({
        defaults: {
            template: 'Dhl_Shipping/checkout/shipping/service-block',
            validateWholeGroup: false
        },

        initialize: function () {
            this._super();

            // add service components to layout
            services.subscribe(function (services) {
                this.destroyChildren();
                var servicesLayout = _.map(services, function (service) {
                    return this.getServiceFieldLayout(service);
                }, this);
                layout(servicesLayout);

                this.elems.extend({rateLimit: { timeout: 50, method: "notifyWhenChangesStop" }});
                this.elems.subscribe(function (elems) {
                    _.each(elems, function (elem) {
                        elem.value.subscribe(function (newValue) {
                            var serviceIndex =  elem.service.code.toLowerCase();
                            serviceModel.addService(serviceIndex, newValue);
                        }, elem)
                    });
                }, this);
            }, this);
        },

        getServiceFieldLayout: function (service) {
            var validation = {};
            _.each(service.validation, function(value, rule) {
                var validatorName = serviceValidationMap.getValidatorName(rule);
                if (validatorName) {
                    validation[validatorName] = value;
                } else {
                    console.warn('DHL service validation rule ' + validation + ' is not defined.');
                }
            });

            return {
                parent: this.name,
                component: 'Dhl_Shipping/js/view/checkout/shipping/service',
                service: service,
                validation: validation
            }
        },

        hasServices: function () {
            return services().length > 0;
        }
    });
});
