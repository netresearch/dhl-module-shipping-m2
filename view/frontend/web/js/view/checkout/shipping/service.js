define([
    'Magento_Ui/js/form/element/abstract',
    'mage/utils/wrapper',
    'Magento_Checkout/js/action/set-shipping-information'
], function (Component, wrapper) {
    'use strict';


    return Component.extend({
        defaults: {
            provider: {},
            autocomplete: '',
            service: {}
        },

        initialize: function() {
            this._super();
            this.initFieldData();
        },

        initFieldData: function () {
            this.template = 'ui/form/field';
            this.elementTmpl = this.getTemplateForType(this.service.inputType);
            this.tooltipTpl = 'Dhl_Shipping/checkout/shipping/tooltip';
            this.label = this.service.name;
            this.placeholder = '';
            this.inputName = this.service.code;
            this.autocomplete = this.service.code;
        },

        getTemplateForType: function (type) {
            var templates = {
                text: 'Dhl_Shipping/checkout/form/element/input',
                checkbox: 'ui/form/element/checkbox',
                time: 'Dhl_Shipping/checkout/form/element/radio',
                date: 'Dhl_Shipping/checkout/form/element/radio'
            };
            if (templates[type]) {
                return templates[type];
            }

            return false;
        }
    });
});
