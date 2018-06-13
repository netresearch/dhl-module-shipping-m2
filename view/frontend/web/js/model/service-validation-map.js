define([
    'Magento_Ui/js/lib/validation/rules'
], function (rulesList) {
    'use strict';

    var rulesMap = {
        'maxLength': 'max_text_length',
        'minLength': 'min_text_length',
        'required': 'required_entry'
    };

    return {
        /**
         * Translate a DHL service input validator into a Magento validator code
         * for use in abstract JS component validation.
         *
         * @param {string} rule
         * @return {string|boolean}
         */
        getValidatorName: function (rule) {
            if (rulesList.hasOwnProperty(rule)) {
                return rule;
            }
            if (rulesMap.hasOwnProperty(rule)) {
                return rulesMap[rule];
            }

            return false;
        }
    };
});
