define([], function () {
    'use strict';

    /**
     * Holds list of information about checkout service compatibilitiy.
     * Items adhere to Dhl\Shipping\Api\Data\ServiceCompatibilityInterface
     *
     * @type {Array}
     */
    var data = [];

    return {
        getData: function() {
            return data;
        },

        setData: function (compatibility) {
            data = compatibility;
        }
    };
});
