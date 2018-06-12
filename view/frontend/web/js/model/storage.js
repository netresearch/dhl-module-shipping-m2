define([
    'jquery'
], function ($) {
    'use strict';

    var storage = $.initNamespaceStorage('dhl_shipping_storage').localStorage;

    return {
        get: function (key) {
            return storage.get(key);
        },

        set: function (key, value) {
            storage.set(key, value);
        },

        clear: function () {
            storage.removeAll();
        }
    };
});
