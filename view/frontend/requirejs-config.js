var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Dhl_Shipping/js/action/set-shipping-information-mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Dhl_Shipping/js/validator-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Dhl_Shipping/js/view/shipping-information-mixin': true
            }
        }
    }
};
