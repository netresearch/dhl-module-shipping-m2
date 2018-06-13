define([], function () {
    'use strict';

    /**
     * Holds list of information about checkout service compatibilitiy.
     * Items adhere to Dhl\Shipping\Api\Data\ServiceCompatibilityInterface
     *
     * @type {Array}
     */
    var compatibility = [];

    return {
        getData: function () {
            return compatibility;
        },

        set: function (data) {
            compatibility = data;
        }
    };
});
