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
 * @category  Dhl
 * @package   Dhl\Shipping
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Service\BulkyGoods;
use Dhl\Shipping\Service\Insurance;
use Dhl\Shipping\Service\ParcelAnnouncement;
use Dhl\Shipping\Service\PrintOnlyIfCodeable;
use Dhl\Shipping\Service\ReturnShipment;
use Dhl\Shipping\Service\VisualCheckOfAge;
use Dhl\Shipping\Util\ShippingRoutesInterface;
use \Magento\Shipping\Model\Config as ShippingConfig;

/**
 * ModuleConfig
 *
 * @category Dhl
 * @package  Dhl\Shipping
 * @author   Christoph Aßmann <christoph.assmann@netresearch.de>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     http://www.netresearch.de/
 */
class ModuleConfig implements ModuleConfigInterface
{

    /**
     * @var ConfigAccessorInterface
     */
    private $configAccessor;

    /**
     * @var ShippingRoutesInterface
     */
    private $routeConfig;

    /**
     * ModuleConfig constructor.
     * @param ConfigAccessorInterface $configAccessor
     * @param ShippingRoutesInterface $routeConfig
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        ShippingRoutesInterface $routeConfig
    ) {
        $this->configAccessor = $configAccessor;
        $this->routeConfig = $routeConfig;
    }

    /**
     * Check if logging is enabled (global scope)
     *
     * @param int $level
     * @return bool
     */
    public function isLoggingEnabled($level = null)
    {
        $level = ($level === null) ? \Monolog\Logger::DEBUG : $level;

        $isEnabled = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_LOGGING_ENABLED);
        $isLevelEnabled = ($this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_LOG_LEVEL) <= $level);

        return ($isEnabled && $isLevelEnabled);
    }

    /**
     * Check if Sandbox mode is enabled in config.
     *
     * @param mixed $store
     * @return bool
     */
    public function isSandboxModeEnabled($store = null)
    {
        return (bool)$this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_SANDBOX_MODE, $store);
    }

    /**
     * Obtain shipper country from shipping origin configuration.
     *
     * @param mixed $store
     * @return string
     */
    public function getShipperCountry($store = null)
    {
        $country = $this->configAccessor->getConfigValue(ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID, $store);
        return $country;
    }

    /**
     * @param mixed $store
     * @return string[]
     */
    public function getEuCountryList($store = null)
    {
        $euCountries = $this->configAccessor->getConfigValue(
            \Magento\Shipping\Helper\Carrier::XML_PATH_EU_COUNTRIES_LIST,
            $store
        );
        return explode(',', $euCountries);
    }

    /**
     * Obtain the shipping method that should be processed with DHL Shipping.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getShippingMethods($store = null)
    {
        $shippingMethods = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DHLMETHODS, $store);
        if (empty($shippingMethods)) {
            $shippingMethods = [];
        } else {
            $shippingMethods = explode(',', $shippingMethods);
        }

        return $shippingMethods;
    }

    /**
     * Obtain the payment methods that should be treated as COD.
     *
     * @param mixed $store
     * @return string[]
     */
    public function getCodPaymentMethods($store = null)
    {
        $codPaymentMethods = $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CODMETHODS, $store);
        if (empty($codPaymentMethods)) {
            $codPaymentMethods = [];
        } else {
            $codPaymentMethods = explode(',', $codPaymentMethods);
        }

        return $codPaymentMethods;
    }

    /**
     * Obtain the default product setting. This is used to highlight one
     * shipping product in case multiple products apply to the current route.
     *
     * @param mixed $store
     * @return string
     */
    public function getDefaultProduct($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_PRODUCT, $store);
    }

    /**
     * Check if the given origin/destination combination can be processed with DHL Shipping.
     *
     * @param string $destinationCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessRoute($destinationCountryId, $store = null)
    {
        $originCountryId = $this->getShipperCountry($store);
        $euCountries = $this->getEuCountryList($store);

        return $this->routeConfig->canProcessRoute($originCountryId, $destinationCountryId, $euCountries);
    }

    /**
     * Check if the given shipping method should be processed with DHL Shipping.
     *
     * @param string $shippingMethod
     * @param mixed $store
     * @return bool
     */
    public function canProcessMethod($shippingMethod, $store = null)
    {
        return in_array($shippingMethod, $this->getShippingMethods($store));
    }

    /**
     * Check if the current order can be shipped with DHL Shipping.
     *
     * @param string $shippingMethod
     * @param string $destCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessShipping($shippingMethod, $destCountryId, $store = null)
    {
        return $this->canProcessMethod($shippingMethod, $store) && $this->canProcessRoute($destCountryId, $store);
    }

    /**
     * Check if the given payment method is cash on delivery.
     *
     * @param string $paymentMethod
     * @param mixed $store
     * @return bool
     */
    public function isCodPaymentMethod($paymentMethod, $store = null)
    {
        return in_array($paymentMethod, $this->getCodPaymentMethods($store));
    }

    /**
     * Get Eu Countries.
     *
     * @param $storeId
     * @return array
     */
    public function getEuCountries($storeId)
    {
        $euCountries = explode(
            ',',
            $this->configAccessor->getConfigValue(
                \Magento\Shipping\Helper\Carrier::XML_PATH_EU_COUNTRIES_LIST,
                $storeId
            )
        );

        return $euCountries;
    }

    /**
     * @param int $destinationCountryId
     * @param int | null $storeId
     * @return bool
     */
    public function isCrossBorderRoute($destinationCountryId, $storeId = null)
    {
        return $this->routeConfig->isCrossBorderRoute(
            $this->getShipperCountry($storeId),
            $destinationCountryId,
            $this->getEuCountries($storeId)
        );
    }

    /**
     * Check if automatic shipment creation is enabled for store
     *
     * @deprecated Not used anywhere
     * @see \Dhl\Shipping\AutoCreate\OrderProvider::load
     * @see \Magento\Store\Model\StoresConfig::getStoresConfigByPath
     *
     * @param null $store
     * @return bool
     */
    public function isAutoCreateEnabled($store = null)
    {
        return (bool)$this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTOCREATE_ENABLED, $store);
    }

    /**
     * Get allowed order statuses for automatic shipment creation
     *
     * @param null $store
     * @return mixed
     */
    public function getAutoCreateOrderStatus($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_CRON_ORDER_STATUS, $store);
    }

    /**
     * Get preselected services for automatic shipping creation
     *
     * @param null $store
     * @return mixed
     */
    public function getAutoCreateServices($store = null)
    {
        $basePath = 'carriers/dhlshipping/shipment_autocreate_service_';
        $basePathGlobal = 'carriers/dhlshipping/bcs_shipment_';
        $autoCreateServices = [];
        $availableServices = [
            BulkyGoods::CODE,
            Insurance::CODE,
            ParcelAnnouncement::CODE,
            ReturnShipment::CODE,
            VisualCheckOfAge::CODE,
            PrintOnlyIfCodeable::CODE
        ];

        foreach ($availableServices as $serviceCode) {
            $configPath = $basePath . strtolower($serviceCode);
            /** @var bool|string $value */
            $value = $this->configAccessor->getConfigValue($configPath, $store);
            if ($value === null){
                // fall back to global path (e.g. for PrintOnlyIfCodeable configValue)
                $configPath = $basePathGlobal . strtolower($serviceCode);
                /** @var bool|string $value */
                $value = $this->configAccessor->getConfigValue($configPath, $store);
            }
            if ($value) {
                $autoCreateServices["service_$serviceCode"] = $value;
            }
        }

        return $autoCreateServices;
    }
}
