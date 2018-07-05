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
 * @author    Christoph Aßmann <christoph.assmann@netresearch.de>
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

namespace Dhl\Shipping\Model\Config;

use Dhl\Shipping\Api\Data\Service\ServiceSettingsInterface;
use Dhl\Shipping\Api\ServicePoolInterface;
use Dhl\Shipping\Model\Service\ServiceOptionProvider;
use Dhl\Shipping\Service\Bcs\BulkyGoods;
use Dhl\Shipping\Service\Bcs\Cod;
use Dhl\Shipping\Service\Bcs\Insurance;
use Dhl\Shipping\Service\Bcs\ParcelAnnouncement;
use Dhl\Shipping\Service\Bcs\PreferredDay;
use Dhl\Shipping\Service\Bcs\PreferredLocation;
use Dhl\Shipping\Service\Bcs\PreferredNeighbour;
use Dhl\Shipping\Service\Bcs\PreferredTime;
use Dhl\Shipping\Service\Bcs\PrintOnlyIfCodeable;
use Dhl\Shipping\Service\Bcs\ReturnShipment;
use Dhl\Shipping\Service\Bcs\VisualCheckOfAge;
use Dhl\Shipping\Model\Adminhtml\System\Config\Source\ApiType;
use Dhl\Shipping\Util\ShippingProducts\ShippingProductsInterface;
use \Magento\Shipping\Model\Config as ShippingConfig;
use Dhl\Shipping\Util\ShippingRoutes\RoutesInterface;

/**
 * ModuleConfig
 *
 * @package  Dhl\Shipping\Model
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
     * @var RoutesInterface
     */
    private $routeConfig;

    /**
     * @var ShippingProductsInterface
     */
    private $shippingProducts;

    /**
     * @var ServiceOptionProvider
     */
    private $serviceOptionProvider;

    /**
     * ModuleConfig constructor.
     *
     * @param ConfigAccessorInterface $configAccessor
     * @param RoutesInterface $routeConfig
     * @param ShippingProductsInterface $shippingProducts
     * @param ServiceOptionProvider $serviceOptionProvider
     */
    public function __construct(
        ConfigAccessorInterface $configAccessor,
        RoutesInterface $routeConfig,
        ShippingProductsInterface $shippingProducts,
        ServiceOptionProvider $serviceOptionProvider
    ) {
        $this->configAccessor = $configAccessor;
        $this->routeConfig = $routeConfig;
        $this->shippingProducts = $shippingProducts;
        $this->serviceOptionProvider = $serviceOptionProvider;
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
     * @param string $recipientCountry
     * @param mixed $store
     * @return string
     */
    public function getDefaultProduct($recipientCountry, $store = null)
    {
        $defaultProducts = json_decode(
            $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_PRODUCTS, $store),
            true
        );
        if (isset($defaultProducts[$recipientCountry])) {
            $defaultProduct = $defaultProducts[$recipientCountry];
        } elseif (in_array($recipientCountry, $this->getEuCountryList())
                && isset($defaultProducts[RoutesInterface::REGION_EU])
        ) {
            $defaultProduct = $defaultProducts[RoutesInterface::REGION_EU];
        } else {
            $defaultProduct = $defaultProducts[RoutesInterface::REGION_INTERNATIONAL];
        }
        return $defaultProduct;
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
        $configuredMethods = $this->getShippingMethods($store);
        foreach ($configuredMethods as $method) {
            if (strpos($shippingMethod, $method) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current order can be shipped with DHL Shipping.
     *
     * @param string $shippingMethod
     * @param string $destCountryId
     * @param mixed $store
     * @return bool
     */
    public function canProcessShipping($shippingMethod, $destCountryId, $store = null): bool
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
    public function isCodPaymentMethod($paymentMethod, $store = null): bool
    {
        return in_array($paymentMethod, $this->getCodPaymentMethods($store));
    }

    /**
     * Get Eu Countries.
     *
     * @param $storeId
     * @return array
     */
    public function getEuCountries($storeId): array
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
     * @param int|null $storeId
     * @return bool
     */
    public function isCrossBorderRoute($destinationCountryId, $storeId = null): bool
    {
        return $this->routeConfig->isCrossBorderRoute(
            $this->getShipperCountry($storeId),
            $destinationCountryId,
            $this->getEuCountries($storeId)
        );
    }

    /**
     * @param string|null $store
     * @return array
     * @throws \ReflectionException
     */
    public function getServiceSettings($store = null): array
    {
        $bulkyGoodsConfig = [
            ServiceSettingsInterface::NAME => 'Bulky Goods', // display name
            ServiceSettingsInterface::IS_ENABLED => true, // general availability of service
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false, // customer can select service
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true, // merchant can select service
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(BulkyGoods::CODE),
                $store
            ), // by default, service is selected for shipment order
            ServiceSettingsInterface::SORT_ORDER => 100,
            ServiceSettingsInterface::OPTIONS => [], // possible service properties
        ];

        $codConfig = [
            ServiceSettingsInterface::NAME => 'Cash On Delivery',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => false,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 110,
        ];

        $insuranceConfig =  [
            ServiceSettingsInterface::NAME => 'Additional Insurance',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(Insurance::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 120,
        ];
        $parcelAnnouncementEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(ParcelAnnouncement::CODE) . '_enabled',
            $store
        );
        $parcelAnnouncementConfig = [
            ServiceSettingsInterface::NAME => 'Parcel Announcement',
            ServiceSettingsInterface::IS_ENABLED => $parcelAnnouncementEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 30,
        ];

        $preferredDayCode = PreferredDay::CODE;
        $preferredDayEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower($preferredDayCode) . '_enabled',
            $store
        );
        $preferredDayConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Day',
            ServiceSettingsInterface::IS_ENABLED => $preferredDayEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::SORT_ORDER => 10,
            ServiceSettingsInterface::OPTIONS => $this->serviceOptionProvider->getPreferredDayOptions()
        ];

        $preferredLocationEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredLocation::CODE) . '_enabled',
            $store
        );
        $preferredLocationConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Location',
            ServiceSettingsInterface::IS_ENABLED => $preferredLocationEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 40,
        ];

        $preferredNeighbourEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredNeighbour::CODE) . '_enabled',
            $store
        );
        $preferredNeighbourConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Neighbour',
            ServiceSettingsInterface::IS_ENABLED => $preferredNeighbourEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 50,
        ];

        $preferredTimeEnabled = (bool) $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/service_' . strtolower(PreferredTime::CODE) . '_enabled',
            $store
        );
        $preferredTimeConfig = [
            ServiceSettingsInterface::NAME => 'Preferred Time',
            ServiceSettingsInterface::IS_ENABLED => $preferredTimeEnabled,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => true,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => false,
            ServiceSettingsInterface::SORT_ORDER => 20,
            ServiceSettingsInterface::OPTIONS => $this->serviceOptionProvider->getPreferredTimeOptions()
        ];

        $printOnlyIfCodeableConfig = [
            ServiceSettingsInterface::NAME => 'Print Only If Codeable',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(PrintOnlyIfCodeable::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 130,
        ];

        $returnShipmentConfig = [
            ServiceSettingsInterface::NAME => 'Return Shipment',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $this->configAccessor->getConfigValue(
                'carriers/dhlshipping/shipment_service_' . strtolower(ReturnShipment::CODE),
                $store
            ),
            ServiceSettingsInterface::OPTIONS => [],
            ServiceSettingsInterface::SORT_ORDER => 140,
        ];

        $visualCheckOfAgeDefault = $this->configAccessor->getConfigValue(
            'carriers/dhlshipping/shipment_service_' . strtolower(VisualCheckOfAge::CODE),
            $store
        );
        $visualCheckOfAgeConfig = [
            ServiceSettingsInterface::NAME => 'Visual Check Of Age',
            ServiceSettingsInterface::IS_ENABLED => true,
            ServiceSettingsInterface::IS_CUSTOMER_SERVICE => false,
            ServiceSettingsInterface::IS_MERCHANT_SERVICE => true,
            ServiceSettingsInterface::IS_SELECTED => (bool) $visualCheckOfAgeDefault,
            ServiceSettingsInterface::OPTIONS => $this->serviceOptionProvider->getVisualCheckOfAgeOptions(),
            ServiceSettingsInterface::PROPERTIES => [
                VisualCheckOfAge::PROPERTY_AGE => $visualCheckOfAgeDefault,
            ],
            ServiceSettingsInterface::SORT_ORDER => 150,
        ];

        return [
            BulkyGoods::CODE => $bulkyGoodsConfig,
            ServicePoolInterface::SERVICE_COD_CODE => $codConfig,
            ServicePoolInterface::SERVICE_INSURANCE_CODE => $insuranceConfig,
            ParcelAnnouncement::CODE => $parcelAnnouncementConfig,
            PreferredDay::CODE => $preferredDayConfig,
            PreferredLocation::CODE => $preferredLocationConfig,
            PreferredNeighbour::CODE => $preferredNeighbourConfig,
            PreferredTime::CODE => $preferredTimeConfig,
            PrintOnlyIfCodeable::CODE => $printOnlyIfCodeableConfig,
            ReturnShipment::CODE => $returnShipmentConfig,
            VisualCheckOfAge::CODE => $visualCheckOfAgeConfig,
        ];
    }

    /**
     * Get allowed order statuses for automatic shipment creation
     *
     * @param null $store
     * @return mixed
     */
    public function getAutoCreateOrderStatus($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTOCREATE_ORDER_STATUS, $store);
    }

    /**
     * Get Notify Customer config.
     *
     * @param null $store
     * @return bool
     */
    public function getAutoCreateNotifyCustomer($store = null)
    {
        return (bool) $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_AUTOCREATE_NOTIFY_CUSTOMER, $store);
    }

    /**
     * Get canonical module version number string
     *
     * @param mixed $store
     * @return string
     */
    public function getModuleVersion($store = null)
    {
        return $this->configAccessor->getConfigValue(ModuleConfigInterface::CONFIG_XML_PATH_MODULE_VERSION, $store);
    }

    /**
     * Get default value of Place of Commital
     *
     * @param mixed $store
     * @return mixed|string
     */
    public function getDefaultPlaceOfCommital($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_PLACE_OF_COMMITAL, $store);
    }

    /**
     * Get the default value of Addtional Fee
     *
     * @param mixed $store
     * @return mixed
     */
    public function getDefaultAdditionalFee($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_ADDITIONAL_FEE, $store);
    }

    /**
     * Get the current Api Type
     *
     * @param mixed $store
     * @return mixed
     */
    public function getApiType($store = null)
    {
        $shippingOrigin = $this->getShipperCountry($store);

        switch ($shippingOrigin) {
            case 'DE':
            case 'AT':
                return ApiType::API_TYPE_BCS;
            default:
                return in_array($shippingOrigin, $this->shippingProducts->getAllCountries())
                    ? ApiType::API_TYPE_GLA
                    : ApiType::API_TYPE_NA;
        }
    }

    /**
     * Get the default value of Terms of Trade
     *
     * @param mixed $store
     * @return mixed
     */
    public function getTermsOfTrade($store = null)
    {
        $api = $this->getApiType($store);
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_TERM_OF_TRADE.'_'.$api, $store);
    }

    /**
     * Get the default value of Export Content Type
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentType($store = null)
    {
        return $this->configAccessor->getConfigValue(self::CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE, $store);
    }

    /**
     * Get default value of Export Content Type Explanation if Content Type is 'Other'
     *
     * @param null $store
     * @return mixed
     */
    public function getDefaultExportContentTypeExplanation($store = null)
    {
        return $this->configAccessor->getConfigValue(
            self::CONFIG_XML_PATH_DEFAULT_EXPORT_CONTENT_TYPE_EXPLANATION,
            $store
        );
    }
}
