/**
 * Dhl Shipping
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * PHP version 7
 *
 * @package   Dhl\Shipping\view
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
define([
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals',
    ], function (Component, totals) {
        "use strict";
        return Component.extend({
            /**
             * @returns {number}
             */
            getValue: function () {
                var price = 0;
                if (totals.getSegment('dhl_service_charge')) {
                    price = totals.getSegment('dhl_service_charge').value;
                }

                return price;
            },
        });
    }
);
