<?php
/**
 * Dhl Versenden
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
 * @category  Dhl
 * @package   Dhl\Versenden
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Versenden\Api;

/**
 * ConfigInterface
 *
 * @category Dhl
 * @package  Dhl\Versenden
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface ConfigInterface
{
    /**
     * Check if logging is enabled
     *
     * @param int $level
     * @return bool
     */
    public function isLoggingEnabled($level = null);

    /**
     * Check if Sandbox mode is enabled.
     *
     * @param mixed $store
     * @return bool
     */
    public function isSandboxModeEnabled($store = null);

    /**
     * @param mixed $store
     * @return mixed
     */
    public function getShipperCountry($store = null);

    /**
     * @param mixed $store
     * @return array
     */
    public function getEuCountryList($store = null);

    /**
     * Obtain the shipping method that should be processed with DHL Versenden.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getShippingMethods($store = null);

    /**
     * Obtain the payment methods that should be treated as COD.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getCodPaymentMethods($store = null);

    /**
     * Check if the given shipping method should be processed with DHL Versenden.
     *
     * @param string $shippingMethod
     * @param mixed $store
     * @return bool
     */
    public function canProcessMethod($shippingMethod, $store = null);

    /**
     * Check if the given payment method is cash on delivery.
     *
     * @param string $paymentMethod
     * @param mixed $store
     * @return bool
     */
    public function isCodPaymentMethod($paymentMethod, $store = null);
}
