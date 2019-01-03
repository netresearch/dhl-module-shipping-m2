define([
    'underscore',
    'ko',
    'Dhl_Shipping/js/model/service-definitions',
    'Dhl_Shipping/js/model/dhl-methods',
    'Dhl_Shipping/js/model/services',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (_, ko, serviceDefinitions, dhlMethods, serviceSelection, quote, $t) {
    'use strict';

    var mixin = {
        defaults: {
            template: 'Dhl_Shipping/shipping-information', // override core template
            serviceInfo: [],
        },

        initialize: function () {
            this._super();
            // turn object into array for knockout template
            this.serviceSelectionObject = serviceSelection.get();
            this.serviceInfo = ko.computed(function () {
                return Object.keys(this.serviceSelectionObject()).map(function (key) {
                    var part = {};
                    part[key] = this.serviceSelectionObject()[key];
                    return part;
                }.bind(this));
            }, this);

            return this;
        },

        hasServiceInfo: function () {
            return this.serviceInfo().length > 0;
        },

        initObservable: function () {
            return this._super().observe('serviceInfo');
        },

        /**
         * Find the service name for a service code.
         *
         * @param serviceSelection
         * @return {string}
         */
        getServiceName: function (serviceSelection) {
            var definitions = serviceDefinitions.get(),
                serviceCode = Object.keys(serviceSelection)[0],
                matchingDefinition = _.find(definitions(), function (item) {
                    return item.code == serviceCode;
                });
            if (matchingDefinition) {
                return $t(matchingDefinition.name);
            } else {
                return serviceCode;
            }
        },

        /**
         * Find the best way to display a service selection value.
         * Handles boolean and select option values.
         * Falls back to raw input value.
         *
         * @param serviceSelection
         * @return {string}
         */
        getFormattedServiceValue: function (serviceSelection) {
            var serviceCode = Object.keys(serviceSelection)[0],
                serviceValues = serviceSelection[serviceCode],
                definitions = serviceDefinitions.get(),
                results = [];

            _.each(serviceValues, function (inputValue, inputCode) {
                var result = inputValue;
                if (inputValue === true) {
                    result = $t('Yes');
                } else if (inputValue === false) {
                    result = $t('No');
                }
                if (definitions().length > 0) {
                    var matchingDefinition = _.find(definitions(), function (item) {
                        return item.code === serviceCode;
                    });
                    var matchingInput = _.find(matchingDefinition.inputs, function (item) {
                        return item.code === inputCode
                    });
                    var option = _.find(matchingInput.options, function (option) {
                        return option.value === inputValue
                    });
                    if (option) {
                        result = option.label;
                    }
                }
                results.push(result);
            });

            return results.join(' ');
        },

        isDhlMethod: function() {
            var carrierCode = quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code;
            var methods = dhlMethods.get();
            return _.contains(methods(), carrierCode);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
