var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Dhl_Shipping/js/action/set-shipping-information-mixin': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Dhl_Shipping/js/action/validator-mixin': true
            }
        }
    }
};
