define([
    'ko'
], function (ko) {
    'use strict';

    var services = {};

    return {

        getServices: function () {
            return services;
        },

        addService:function (name, value) {
            services[name] = value;
        }
    };
});
