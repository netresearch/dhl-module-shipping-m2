<?php
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
 * @package   Dhl\Shipping\Model
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Config;

/**
 * ServiceConfig
 *
 * @package  Dhl\Shipping\Model
 * @author   Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface ServiceConfigInterface
{
    const CONFIG_XML_FIELD_CUT_OFF_TIME_CONFIG = 'carriers/dhlshipping/service_cutoff_time';
    const CONFIG_XML_FIELD_EXCLUDED_DROPOFFDAYS = 'carriers/dhlshipping/service_drop_off_days';

    /**
     * Obtain drop off days froms config.
     *
     * @param null $store
     * @return string[]
     */
    public function getExcludedDropOffDays($store = null);

    /**
     * @param null $store
     * @return string[]
     */
    public function getCutOffTime($store = null);
}
