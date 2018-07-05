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
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */
namespace Dhl\Shipping\Model\Config;

/**
 * ModuleConfigInterface
 *
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
interface ModuleConfigInterface
{
    const CONFIG_XML_PATH_TITLE = 'carriers/dhlshipping/title';
    const CONFIG_XML_PATH_LOG_LEVEL = 'carriers/dhlshipping/log_level';
    const CONFIG_XML_PATH_DHLMETHODS = 'carriers/dhlshipping/shipment_dhlmethods';
    const CONFIG_XML_PATH_CODMETHODS = 'carriers/dhlshipping/shipment_dhlcodmethods';
    const CONFIG_XML_PATH_LOGGING_ENABLED = 'carriers/dhlshipping/logging_enabled';
    const CONFIG_XML_PATH_DEFAULT_PRODUCTS = 'carriers/dhlshipping/default_shipping_products';
    const CONFIG_XML_PATH_SANDBOX_MODE = 'carriers/dhlshipping/sandbox_mode';
    const CONFIG_XML_PATH_MODULE_VERSION = 'modules/Dhl_Shipping/version';

    const CONFIG_XML_PATH_AUTOCREATE_ENABLED = 'carriers/dhlshipping/shipment_autocreate_enabled';
    const CONFIG_XML_PATH_AUTOCREATE_ORDER_STATUS = 'carriers/dhlshipping/shipment_autocreate_order_status';
    const CONFIG_XML_PATH_AUTOCREATE_NOTIFY_CUSTOMER = 'carriers/dhlshipping/shipment_autocreate_send_shippinginfo';
    const CONFIG_XML_PATH_DEFAULT_ADDITIONAL_FEE = 'carriers/dhlshipping/shipmemt_autocreate_addtionalfee';
    const CONFIG_XML_PATH_DEFAULT_PLACE_OF_COMMITAL = 'carriers/dhlshipping/shipmemt_autocreate_placeofcommital';
    const CONFIG_XML_PATH_API_TYPE = 'carriers/dhlshipping/api_type';
    const CONFIG_XML_PATH_DEFAULT_TERM_OF_TRADE = 'carriers/dhlshipping/shipmemt_autocreate_termsoftrade';
    const CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE = 'carriers/dhlshipping/shipment_autocreate_export_contenttype';
    const CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE_EXPLANATION  =
        'carriers/dhlshipping/shipment_autocreate_export_contenttype_explanation';

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
     * @return array
     */
    public function getEuCountryList($store = null);

    /**
     * Obtain the shipping method that should be processed with DHL Shipping.
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
     * Obtain the default product setting. This is used to highlight one
     * shipping product in case multiple products apply to the current route.
     *
     * @param mixed $recipientCountry
     * @param mixed $store
     * @return string
     */
    public function getDefaultProduct($recipientCountry, $store = null);

    /**
     * Obtain shipper country from shipping origin configuration.
     *
     * @param mixed $store
     * @return string
     */
    public function getShipperCountry($store = null);

    /**
     * Check if the given origin/destination combination can be processed with DHL Shipping.
     *
     * @see canProcessShipping()
     * @param string $destinationCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessRoute($destinationCountryId, $store = null);

    /**
     * Check if the given shipping method should be processed with DHL Shipping.
     *
     * @see canProcessShipping()
     * @param string $shippingMethod
     * @param mixed $store
     * @return bool
     */
    public function canProcessMethod($shippingMethod, $store = null);

    /**
     * Check if the current order can be shipped with DHL Shipping (incl. shipping method and route).
     *
     * @param string $shippingMethod
     * @param string $destCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessShipping($shippingMethod, $destCountryId, $store = null);

    /**
     * Check if the given payment method is cash on delivery.
     *
     * @param string $paymentMethod
     * @param mixed $store
     * @return bool
     */
    public function isCodPaymentMethod($paymentMethod, $store = null);

    /**
     * Checks if the route is crossing borders for given store configuration
     *
     * @param int $destinationCountryId
     * @param int|null $storeId
     * @return bool
     */
    public function isCrossBorderRoute($destinationCountryId, $storeId = null);

    /**
     * Get allowed order statuses for automatic shipment creation
     *
     * @param mixed $store
     * @return mixed
     */
    public function getAutoCreateOrderStatus($store = null);

    /**
     * Get Notify Customer config.
     *
     * @param mixed $store
     * @return bool
     */
    public function getAutoCreateNotifyCustomer($store = null);

    /**
     * Get canonical module version number string
     *
     * @param mixed $store
     * @return string
     */
    public function getModuleVersion($store = null);

    /**
     * Get default value for PLace of Commital
     *
     * @param mixed $store
     * @return string
     */
    public function getDefaultPlaceOfCommital($store = null);

    /**
     * Get default value of Additional Fee
     *
     * @param mixed $store
     * @return mixed
     */
    public function getDefaultAdditionalFee($store = null);

    /**
     * Infer Api Type of a store
     *
     * @param null $store
     * @return mixed
     */
    public function getApiType($store = null);

    /**
     * Get the default value of Terms of Trade
     *
     * @param null $store
     * @return mixed
     */
    public function getTermsOfTrade($store = null);

    /**
     * Get the default value of Export Content Type
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentType($store = null);

    /**
     * Get default value of Export Content Type Explanation if Content Type is 'Other'
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentTypeExplanation($store = null);
}
