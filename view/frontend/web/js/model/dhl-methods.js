define([
    'ko',
], function (ko) {
    'use strict';

    var methods = ko.observable({});

    /**
     * @type {Object}
     */
    return {
        get: function () {
            return methods;
        },

        set: function (data) {
            methods(data)
        },
    };
});
