
define([
    'underscore',
    'ko',
    'Magento_Customer/js/customer-data'
], function (_, ko, customerData) {
    'use strict';

    var cacheKey = 'services-data';

    return {
        getDhlMethods: function () {
            var sectionData = customerData.get(cacheKey);
            return sectionData().methods;
        }
    };
});
