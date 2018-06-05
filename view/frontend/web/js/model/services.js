define([
    'ko'
], function (ko) {
    'use strict';

    var services = {};

    return {

        getServices: function () {
            return services;
        },

        addService:function (name, code, value) {
            if (services[name] == undefined) {
                services[name] = {};
            }
            services[name][code] = value;
            console.log(services);
        }
    };
});
