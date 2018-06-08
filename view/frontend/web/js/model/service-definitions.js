define([
    'ko',
], function (ko) {
    'use strict';

    var services = ko.observable({});

    /**
     * Holds list of definitions for checkout services.
     * Items adhere to Dhl\Shipping\Api\Data\ServiceInterface;
     *
     * @type {Object}
     */
    return {
        get: function () {
            return services;
        },

        set: function (data) {
            services(data)
        },
    };
});
