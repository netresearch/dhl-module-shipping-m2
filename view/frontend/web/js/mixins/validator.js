define([
    'underscore',
    'mage/translate'
], function (_, $t) {
    'use strict';

    return function (validator) {

        var isOnBlacklist = function (value, blacklist) {
                return undefined !== _.find(blacklist, function (blacklistItem) {
                    return value.toLowerCase().indexOf(blacklistItem) !== -1;
                })
            },
            packingStationWords = [
                'paketbox', 'packstation', 'postfach', 'postfiliale', 'filiale', 'paketkasten', 'dhlpaketstation',
                'parcelshop', 'pakcstation', 'paackstation', 'pakstation', 'backstation', 'bakstation', 'wunschfiliale', 'deutsche post'],
            specialChars = ['<', '>', '\\n', '\\r', '\\', '\'', '"', ';', '+'];

        validator.addRule(
            'dhl_filter_packing_station',
            function (value, params) {
                return !isOnBlacklist(value, packingStationWords);
            },
            $t('You must not refer to a packing station, postal office, or similar.')
        );

        validator.addRule(
            'dhl_filter_special_chars',
            function (value, params) {
                return !isOnBlacklist(value, specialChars);
            },
            $t('Your input must not include one of the following: ') + specialChars.join(' ')
        );

        return validator;
    };
});
