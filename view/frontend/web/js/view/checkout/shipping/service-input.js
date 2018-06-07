define([
    'Magento_Ui/js/form/element/abstract',
    'Dhl_Shipping/js/model/services',
    'Dhl_Shipping/js/model/service-validation-map'
], function (Component, serviceModel, serviceValidationMap) {
    'use strict';

    return Component.extend({
        defaults: {
            provider: {},
            autocomplete: '',
            serviceInput: {},
            service: {},
            lastValue: '',
        },

        initialize: function() {
            this._super();
            this.initFieldData();

            this.value.subscribe(function (value) {
                if (value) {
                    serviceModel.addService(
                        this.service.code,
                        this.serviceInput.code,
                        value
                    );
                } else {
                    serviceModel.removeService(
                        this.service.code,
                        this.serviceInput.code
                    );
                }
            }.bind(this));
        },

        initFieldData: function () {
            var validationData = {};
            _.each(this.serviceInput.validationRules, function (value, rule) {
                var validatorName = serviceValidationMap.getValidatorName(rule);
                if (validatorName) {
                    validationData[validatorName] = value;
                } else {
                    console.warn('DHL service validation rule ' + rule + ' is not defined.');
                }
            });

            this.validation = validationData;
            this.template = 'Dhl_Shipping/checkout/form/field';
            this.elementTmpl = this.getTemplateForType(this.serviceInput.inputType);
            this.label = this.description = this.serviceInput.label;
            this.placeholder = this.serviceInput.placeholder;
            if (this.serviceInput.tooltip) {
                this.tooltip = {description: this.serviceInput.tooltip};
            }
            this.inputName = this.serviceInput.code;
            this.autocomplete = this.serviceInput.code;
        },

        getTemplateForType: function (type) {
            var templates = {
                text: 'ui/form/element/input',
                checkbox: 'ui/form/element/checkbox',
                time: 'Dhl_Shipping/checkout/form/element/radio',
                date: 'Dhl_Shipping/checkout/form/element/radio'
            };
            if (templates[type]) {
                return templates[type];
            }

            return false;
        },

        /**
         * Unselect the radio set when an already selected item is clicked.
         *
         * @return {boolean}
         */
        handleRadioUnselect: function () {
            if (this.lastValue === this.value()) {
                this.value('');
            }
            this.lastValue = this.value();
            return true;
        },

        onUpdate: function () {
            this._super();
        }
    });
});
