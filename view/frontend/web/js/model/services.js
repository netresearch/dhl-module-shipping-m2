define([
    'underscore'
], function (_) {
    'use strict';

    var services = {};

    return {

        getServices: function () {
            return services;
        },

        addService: function (name, code, value) {
            if (services[name] == undefined) {
                services[name] = {};
            }
            services[name][code] = value;
        },

        removeService: function (name, code) {
            delete services[name][code];
            if (_.isEmpty(services[name])) {
                delete services[name];
            }
        }
    };
});
