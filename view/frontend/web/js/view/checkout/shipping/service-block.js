define([
    'underscore',
    'uiCollection',
    'uiLayout',
    'uiRegistry',
    'mage/translate',
    'Dhl_Shipping/js/action/get-services',
    'Dhl_Shipping/js/model/service-compatibility',
    'Magento_Checkout/js/model/quote'
], function (_, UiCollection, layout, registry, $t, serviceAction, serviceCompatibility, quote) {
    'use strict';

    return UiCollection.extend({
        defaults: {
            services: [],
            template: 'Dhl_Shipping/checkout/shipping/service-block',
            error: '',
        },

        initialize: function () {
            this._super();

            quote.shippingMethod.subscribe(function () {
                var countryId = quote.shippingAddress().countryId;
                var carrierCode = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
                if (countryId && carrierCode) {
                    serviceAction(countryId, carrierCode, function (result) {
                        this.services(result.services);
                        serviceCompatibility.setData(result.compatibility);
                    }.bind(this));
                }
            }.bind(this));

            // add service components to layout
            this.services.subscribe(function (services) {
                this.destroyChildren();
                var servicesLayout = _.map(services, function (service) {
                    return this.getServiceFieldLayout(service);
                }, this);
                layout(servicesLayout);
                this.elems.extend({rateLimit: { timeout: 50, method: "notifyWhenChangesStop" }});
            }, this);
        },

        initObservable: function () {
            this._super();
            this.observe('services error');

            return this;
        },

        getServiceFieldLayout: function (service) {
            return {
                parent: this.name,
                component: 'Dhl_Shipping/js/view/checkout/shipping/service',
                service: service
            }
        },

        hasServices: function () {
            return this.services().length > 0;
        },


        triggerValidationFail: function (rule) {
            var message = $t('Invalid service combination.');
            if (rule.type === 'inclusive') {
                message = $t('Invalid service combination. Services %1 must be booked together.').replace(
                    '%1',
                    rule.subject.join(' ' + $t('and') + ' ')
                );
            } else if (rule.type === 'exclusive') {
                message = $t('Invalid service combination. Services %1 must not be booked together.').replace(
                    '%1',
                    rule.subject.join(' ' + $t('and') + ' ')
                );
            }
            this.error(message);
            this.bubble('error', message);
        },

        triggerValidationSuccess: function () {
            this.error('');
        },
    });
});
