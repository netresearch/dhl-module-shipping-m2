define([
    'Magento_Ui/js/form/element/abstract',
    'Dhl_Shipping/js/model/services'
], function (Component, serviceModel) {
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
                    var serviceName = this.service.code.toLowerCase();
                    serviceModel.addService(
                        serviceName,
                        this.serviceInput.code,
                        value
                    );
                }
            }.bind(this));
        },

        initFieldData: function () {
            this.template = 'ui/form/field';
            this.elementTmpl = this.getTemplateForType(this.serviceInput.inputType);
            this.tooltipTpl = 'Dhl_Shipping/checkout/shipping/tooltip';
            this.label = this.description = this.serviceInput.label;
            this.placeholder = this.serviceInput.placeholder;
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
