var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Dhl_Shipping/js/mixins/set-shipping-information': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Dhl_Shipping/js/mixins/validator': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Dhl_Shipping/js/mixins/shipping-information': true
            }
        }
    }
};
