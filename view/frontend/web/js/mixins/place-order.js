define([
    'mage/utils/wrapper',
    'Dhl_Shipping/js/model/storage'
], function (wrapper, storage) {
    'use strict';

    /** @see 'Magento_Checkout/js/action/place-order' */
    return function (placeOrder) {
        return wrapper.wrap(placeOrder, function (origFunc, paymentData, messageContainer) {
            return origFunc(paymentData, messageContainer).success(storage.clear);
        });
    };
});
