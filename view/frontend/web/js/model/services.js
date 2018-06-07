define([
    'underscore',
    'ko',
    'Dhl_Shipping/js/model/storage'
], function (_, ko, storage) {
    'use strict';

    var services = storage.get('cachedServiceValues') ? ko.observable(storage.get('cachedServiceValues')) : ko.observable({});

    return {

        get: function () {
            return services;
        },

        addService: function (name, code, value) {
            var workingCopy = services();
            if (workingCopy[name] == undefined) {
                workingCopy[name] = {};
            }
            workingCopy[name][code] = value;
            storage.set('cachedServiceValues', workingCopy);
            services(workingCopy);
        },

        removeService: function (name, code) {
            var workingCopy = services();
            delete workingCopy[name][code];
            if (_.isEmpty(workingCopy[name])) {
                delete workingCopy[name];
            }
            storage.set('cachedServiceValues', workingCopy);
            services(workingCopy);
        }
    };
});
