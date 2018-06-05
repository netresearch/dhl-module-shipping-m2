define([
    'underscore',
    'uiCollection',
    'uiLayout',
    'ko',
    'Dhl_Shipping/js/action/get-services',
    'Magento_Checkout/js/model/quote'
], function (_, UiCollection, layout, ko, serviceAction, quote) {

    'use strict';

    var services = ko.observableArray([]);

    var compatibility = [];

    quote.shippingMethod.subscribe(function () {
        var countryId = quote.shippingAddress().countryId;
        var carrierCode = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
        if (countryId && carrierCode) {
            serviceAction(countryId, carrierCode, function (result) {
                services(result[0].services);
                compatibility = result[0].compatibility;
            });
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
            }, this);
        },

        getServiceFieldLayout: function (service) {
            return {
                parent: this.name,
                component: 'Dhl_Shipping/js/view/checkout/shipping/service',
                service: service
            }
        },

        hasServices: function () {
            return services().length > 0;
        }
    });
});
